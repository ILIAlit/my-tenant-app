import { Link, usePage } from '@inertiajs/react';
import { Edit, FileText, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import AdminContractsController from '@/actions/App/Http/Controllers/Admin/Contracts/AdminContractsController';
import AdminContractFormDialog from '@/components/contracts/admin-contract-form-dialog';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import type { Contract, Rooms } from '@/types';

type PageProps = {
    room: Rooms;
    contracts: Contract[];
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

export default function AdminRoomContractsPage() {
    const { room, contracts } = usePage<PageProps>().props;
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editing, setEditing] = useState<Contract | null>(null);

    const openCreate = () => {
        setEditing(null);
        setDialogOpen(true);
    };

    const openEdit = (contract: Contract) => {
        setEditing(contract);
        setDialogOpen(true);
    };

    return (
        <>
            <div className="flex items-center justify-between">
                <PageHeader
                    title="Договоры"
                    description={`История договоров комнаты №${room.number}`}
                />
                <Button onClick={openCreate} className="gap-2">
                    <Plus size={16} />
                    Добавить
                </Button>
            </div>

            {contracts.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    Договоров пока нет
                </div>
            ) : (
                <div className="space-y-4">
                    {contracts.map((contract) => (
                        <div
                            key={contract.id}
                            className="rounded-xl border border-gray-200 bg-white"
                        >
                            <div className="flex items-center justify-between border-b border-gray-200 px-5 py-3">
                                <h3 className="font-medium">
                                    № {contract.number}
                                </h3>
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        className="gap-1"
                                        onClick={() => openEdit(contract)}
                                    >
                                        <Edit size={14} />
                                        Изменить
                                    </Button>
                                    <Link
                                        href={AdminContractsController.delete([
                                            contract.id,
                                            room.id,
                                        ])}
                                        as="button"
                                        method="delete"
                                        preserveScroll
                                        className="rounded-lg bg-red-50 px-3 py-2 text-red-600 hover:bg-red-100"
                                    >
                                        <Trash2 size={14} />
                                    </Link>
                                </div>
                            </div>
                            <div className="space-y-3 p-5 text-sm">
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <span className="text-gray-500">
                                            Дата заключения
                                        </span>
                                        <p>
                                            {formatDate(
                                                contract.conclusion_date,
                                            )}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">
                                            Срок действия
                                        </span>
                                        <p>
                                            {formatDate(
                                                contract.expiration_date,
                                            )}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span className="text-gray-500">
                                        Условия оплаты
                                    </span>
                                    <p className="whitespace-pre-line">
                                        {contract.payment_terms}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-gray-500">
                                        Условия расторжения
                                    </span>
                                    <p className="whitespace-pre-line">
                                        {contract.termination_terms}
                                    </p>
                                </div>
                                {contract.file_url && (
                                    <a
                                        href={contract.file_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-2 text-sm font-medium text-primary underline"
                                    >
                                        <FileText size={16} />
                                        Открыть файл
                                    </a>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}

            <AdminContractFormDialog
                open={dialogOpen}
                onOpenChange={setDialogOpen}
                roomId={room.id}
                contract={editing}
            />
        </>
    );
}

import { usePage } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import PageHeader from '@/components/ui/page-header';
import type { Contract } from '@/types';

type PageProps = {
    contracts: Contract[];
};

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

export default function ContractsPage() {
    const { contracts } = usePage<PageProps>().props;

    return (
        <>
            <PageHeader
                title="Договоры"
                description="Договоры по вашим комнатам"
            />

            {contracts.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    У вас пока нет договоров
                </div>
            ) : (
                <div className="space-y-4">
                    {contracts.map((contract) => (
                        <div
                            key={contract.id}
                            className="max-w-2xl rounded-xl border border-gray-200 bg-white"
                        >
                            <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                                <h3 className="font-medium">
                                    Договор № {contract.number}
                                </h3>
                                {contract.room && (
                                    <span className="text-sm text-gray-600">
                                        Комната №{contract.room.number}
                                    </span>
                                )}
                            </div>
                            <div className="space-y-3 p-6 text-sm">
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
                                        className="inline-flex items-center gap-2 font-medium text-primary underline"
                                    >
                                        <FileText size={16} />
                                        Открыть файл договора
                                    </a>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </>
    );
}

import { Head, Link, usePage } from '@inertiajs/react';
import { FileText, Settings2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import contracts from '@/routes/contracts';
import type { Contract, InvoiceRenter } from '@/types';

type PageProps = {
    contracts: Contract[];
};

const renterName = (renter?: InvoiceRenter | null) =>
    renter
        ? [renter.last_name, renter.name, renter.middle_name]
              .filter(Boolean)
              .join(' ')
        : '—';

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('ru-RU');

export default function AdminContractsPage() {
    const { contracts: items } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Договоры" />

            <PageHeader
                title="Договоры"
                description="Все договоры по комнатам"
            />

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">
                        Список договоров ({items.length})
                    </h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Номер
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Комната
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Арендатор
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Заключён
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Действует до
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Файл
                                </th>
                                <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        Договоров пока нет
                                    </td>
                                </tr>
                            ) : (
                                items.map((contract) => (
                                    <tr
                                        key={contract.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4 font-medium">
                                            № {contract.number}
                                        </td>
                                        <td className="px-6 py-4">
                                            {contract.room
                                                ? `№${contract.room.number}`
                                                : '—'}
                                        </td>
                                        <td className="px-6 py-4">
                                            {renterName(contract.room?.user)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {formatDate(
                                                contract.conclusion_date,
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {formatDate(
                                                contract.expiration_date,
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            {contract.file_url ? (
                                                <a
                                                    href={contract.file_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1 text-sm font-medium text-primary underline"
                                                >
                                                    <FileText size={16} />
                                                    Открыть
                                                </a>
                                            ) : (
                                                '—'
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex justify-end">
                                                {contract.room && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        className="gap-1"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={contracts.adminGet(
                                                                contract.room.id,
                                                            )}
                                                        >
                                                            <Settings2
                                                                size={16}
                                                            />
                                                            Управление
                                                        </Link>
                                                    </Button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

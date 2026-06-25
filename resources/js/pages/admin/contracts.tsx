import { Head, Link, usePage } from '@inertiajs/react';
import { ExternalLink, FileText } from 'lucide-react';
import { Button } from '@/components/ui/button';
import contracts from '@/routes/contracts';
import renters from '@/routes/renters';
import type { ContractListItem } from '@/types';
import { roomWithFloorLabel } from '@/types';

type PageProps = {
    contracts: ContractListItem[];
};

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : '—';

const formatRoom = (contract: ContractListItem): string => {
    if (!contract.renter.room) {
        return '—';
    }

    return roomWithFloorLabel(contract.renter.room);
};

export default function ContractsPage() {
    const { contracts: contractsList } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Договоры" />

            <div className="mb-6">
                <h1 className="mb-2 text-2xl font-semibold">Договоры</h1>
                <p className="text-gray-500">
                    Все договоры аренды арендаторов
                </p>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-6 py-4">
                    <h3 className="font-medium">Список договоров</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Номер
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Арендатор
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Комната
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Период
                                </th>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                    Плата
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
                            {contractsList.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-6 py-10 text-center text-gray-500"
                                    >
                                        Договоров пока нет
                                    </td>
                                </tr>
                            ) : (
                                contractsList.map((contract) => (
                                    <tr
                                        key={contract.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4 font-medium">
                                            {contract.number}
                                        </td>
                                        <td className="px-6 py-4">
                                            <Link
                                                href={renters.settings(
                                                    contract.renter.id,
                                                )}
                                                className="hover:underline"
                                            >
                                                {contract.renter.full_name}
                                            </Link>
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatRoom(contract)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {formatDate(contract.start_date)} —{' '}
                                            {formatDate(contract.end_date)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {contract.monthly_rent.toFixed(2)}{' '}
                                            BYN
                                        </td>
                                        <td className="px-6 py-4">
                                            {contract.file_url ? (
                                                <a
                                                    href={contract.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex items-center gap-1 text-sm text-primary hover:underline"
                                                >
                                                    <FileText size={16} />
                                                    {contract.file_name ??
                                                        'Файл'}
                                                </a>
                                            ) : (
                                                '—'
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={renters.settings(
                                                            contract.renter
                                                                .id,
                                                        )}
                                                    >
                                                        <ExternalLink
                                                            size={16}
                                                        />
                                                        Открыть
                                                    </Link>
                                                </Button>
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

ContractsPage.layout = {
    breadcrumbs: [
        {
            title: 'Договоры',
            href: contracts.get(),
        },
    ],
};

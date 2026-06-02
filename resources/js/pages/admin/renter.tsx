import { Head, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import renters from '@/routes/renters';
//import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';

export default function Dashboard() {
    const page = usePage();
    console.log(page);

    return (
        <>
            <Head title="Dashboard" />
            <div className="p-8">
                <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="mb-2 text-2xl font-semibold">
                            Арендаторы
                        </h1>
                        <p className="text-gray-400">
                            Управление арендаторами и контактами
                        </p>
                    </div>
                    <Button>
                        <Plus size={20} />
                        Добавить арендатора
                    </Button>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white">
                    <div className="border-b border-gray-200 px-6 py-4">
                        <h3 className="font-medium">Список арендаторов</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        ФИО
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        Квартира
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        Телефон
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        Email
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        Аренда
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        Долг
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-600">
                                        Статус
                                    </th>
                                    <th className="px-6 py-3 text-right text-sm font-medium text-gray-600">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            {/* <tbody>
                                {TENANTS.map((tenant) => (
                                    <tr
                                        key={tenant.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4 font-medium">
                                            {tenant.name}
                                        </td>
                                        <td className="px-6 py-4">
                                            {tenant.apartment}
                                        </td>
                                        <td className="px-6 py-4">
                                            {tenant.phone}
                                        </td>
                                        <td className="px-6 py-4">
                                            {tenant.email}
                                        </td>
                                        <td className="px-6 py-4">
                                            {tenant.rent.toLocaleString()} ₽
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={
                                                    tenant.debt > 0
                                                        ? 'font-medium text-red-600'
                                                        : 'text-gray-400'
                                                }
                                            >
                                                {tenant.debt > 0
                                                    ? `${tenant.debt.toLocaleString()} ₽`
                                                    : '—'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            {getStatusBadge(tenant.status)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2">
                                                <button className="rounded p-1 hover:bg-gray-100">
                                                    <Eye
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </button>
                                                <button className="rounded p-1 hover:bg-gray-100">
                                                    <Edit
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </button>
                                                <button className="rounded p-1 hover:bg-gray-100">
                                                    <Trash2
                                                        size={18}
                                                        className="text-red-600"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody> */}
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Арендаторы',
            href: renters.get(),
        },
    ],
};

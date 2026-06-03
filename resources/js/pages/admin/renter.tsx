import { Head, usePage } from '@inertiajs/react';
import { Edit, Eye, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import renters from '@/routes/renters';
import type { User } from '@/types';
//import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';

type PageProps = {
    renters: User[];
};

export default function Dashboard() {
    const { renters } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Dashboard" />
            <div className="p-8">
                <PageHeader
                    title="Арендаторы"
                    description="Управление арендаторами и контактами"
                />

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
                            <tbody>
                                {renters.map((renter) => (
                                    <tr
                                        key={renter.id}
                                        className="border-b border-gray-100 hover:bg-gray-50"
                                    >
                                        <td className="px-6 py-4 font-medium">
                                            {renter.last_name} {renter.name}{' '}
                                            {renter.middle_name}
                                        </td>
                                        <td className="px-6 py-4">{}</td>
                                        <td className="px-6 py-4">
                                            {renter.phone}
                                        </td>
                                        <td className="px-6 py-4">
                                            {renter.email}
                                        </td>
                                        <td className="px-6 py-4">{} ₽</td>
                                        <td className="px-6 py-4"></td>
                                        <td className="px-6 py-4">{}</td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button className="rounded p-1 hover:bg-gray-100">
                                                    <Eye
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </Button>
                                                <Button className="rounded p-1 hover:bg-gray-100">
                                                    <Edit
                                                        size={18}
                                                        className="text-gray-600"
                                                    />
                                                </Button>
                                                <Button className="rounded p-1 hover:bg-gray-100">
                                                    <Trash2
                                                        size={18}
                                                        className="text-red-600"
                                                    />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
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

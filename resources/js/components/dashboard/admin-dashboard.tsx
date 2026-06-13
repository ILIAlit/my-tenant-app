import { Building2, CreditCard, UserX } from 'lucide-react';
import { cn } from '@/lib/utils';
import type {
    AdminDashboardStats,
    DashboardRoom,
    RoomStatus,
} from '@/types/dashboard/dashboard';
import type { PaymentStatus } from '@/types/payments/payments';

const formatMoney = (value: number) =>
    `${new Intl.NumberFormat('ru-RU').format(value)} ₽`;

const formatDateTime = (value: string | null) =>
    value
        ? new Date(value).toLocaleString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '';

const roomStatusStyle: Record<
    RoomStatus,
    { tile: string; label: string }
> = {
    free: {
        tile: 'border-green-200 bg-green-50 text-green-900',
        label: 'Свободна',
    },
    used: {
        tile: 'border-blue-200 bg-blue-50 text-blue-900',
        label: 'Занята',
    },
    repair: {
        tile: 'border-amber-200 bg-amber-50 text-amber-900',
        label: 'Ремонт',
    },
};

const paymentBadge: Record<PaymentStatus, { className: string; label: string }> =
    {
        review: { className: 'bg-yellow-100 text-yellow-800', label: 'На проверке' },
        approved: { className: 'bg-green-100 text-green-800', label: 'Одобрен' },
        rejected: { className: 'bg-red-100 text-red-800', label: 'Отклонён' },
    };

function RoomTile({ room }: { room: DashboardRoom }) {
    const style = roomStatusStyle[room.status];

    return (
        <div
            className={cn(
                'flex min-w-24 flex-col rounded-lg border p-3',
                style.tile,
            )}
            title={room.tenant ?? style.label}
        >
            <span className="text-sm font-semibold">№ {room.number}</span>
            <span className="mt-1 truncate text-xs opacity-80">
                {room.tenant ?? style.label}
            </span>
        </div>
    );
}

export default function AdminDashboard({
    stats,
}: {
    stats: AdminDashboardStats;
}) {
    const { floors, roomStats, recentPayments, debtors } = stats;

    const totalDebt = debtors.reduce((sum, debtor) => sum + debtor.debt, 0);

    return (
        <>
            <div className="mt-6 grid gap-4 md:grid-cols-2">
                <div className="rounded-xl border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <span className="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                            <Building2 className="size-5 text-blue-600" />
                        </span>
                        <span className="text-sm font-medium text-gray-500">
                            Комнаты
                        </span>
                    </div>
                    <p className="mt-4 text-2xl font-semibold text-gray-900">
                        {roomStats.total}
                    </p>
                    <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500">
                        <span className="text-green-700">
                            Свободно: {roomStats.free}
                        </span>
                        <span className="text-blue-700">
                            Занято: {roomStats.used}
                        </span>
                        <span className="text-amber-700">
                            Ремонт: {roomStats.repair}
                        </span>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <span className="flex size-10 items-center justify-center rounded-lg bg-red-100">
                            <UserX className="size-5 text-red-600" />
                        </span>
                        <span className="text-sm font-medium text-gray-500">
                            Должники
                        </span>
                    </div>
                    <p className="mt-4 text-2xl font-semibold text-gray-900">
                        {debtors.length}
                    </p>
                    <p className="mt-2 text-sm text-gray-500">
                        Сумма долга: {formatMoney(totalDebt)}
                    </p>
                </div>
            </div>

            <section className="mt-6 rounded-xl border border-gray-200 bg-white">
                <div className="border-b border-gray-200 px-5 py-4">
                    <div className="flex items-center gap-2">
                        <Building2 className="size-5 text-gray-500" />
                        <h2 className="font-semibold">План дома</h2>
                    </div>
                </div>

                {floors.length === 0 ? (
                    <div className="px-5 py-10 text-center text-sm text-gray-500">
                        Комнаты ещё не добавлены
                    </div>
                ) : (
                    <div className="space-y-6 p-5">
                        {floors.map((floor) => (
                            <div key={floor.floor}>
                                <h3 className="mb-3 text-sm font-medium text-gray-500">
                                    Этаж {floor.floor}
                                </h3>
                                <div className="flex flex-wrap gap-3">
                                    {floor.rooms.map((room) => (
                                        <RoomTile key={room.id} room={room} />
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </section>

            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <section className="rounded-xl border border-gray-200 bg-white">
                    <div className="flex items-center gap-2 border-b border-gray-200 px-5 py-4">
                        <CreditCard className="size-5 text-gray-500" />
                        <h2 className="font-semibold">Последние платежи</h2>
                    </div>

                    {recentPayments.length === 0 ? (
                        <div className="px-5 py-10 text-center text-sm text-gray-500">
                            Платежей пока нет
                        </div>
                    ) : (
                        <ul className="divide-y divide-gray-100">
                            {recentPayments.map((payment) => (
                                <li
                                    key={payment.id}
                                    className="flex items-center justify-between gap-4 px-5 py-3"
                                >
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {payment.tenant ?? '—'}
                                        </p>
                                        <p className="mt-0.5 truncate text-xs text-gray-500">
                                            {payment.invoice_name ?? '—'} ·{' '}
                                            {formatDateTime(payment.created_at)}
                                        </p>
                                    </div>
                                    <div className="flex flex-col items-end gap-1">
                                        <span className="font-semibold">
                                            {formatMoney(payment.amount)}
                                        </span>
                                        <span
                                            className={cn(
                                                'rounded-full px-2 py-0.5 text-xs font-medium',
                                                paymentBadge[payment.status]
                                                    ?.className,
                                            )}
                                        >
                                            {paymentBadge[payment.status]?.label}
                                        </span>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="rounded-xl border border-gray-200 bg-white">
                    <div className="flex items-center gap-2 border-b border-gray-200 px-5 py-4">
                        <UserX className="size-5 text-gray-500" />
                        <h2 className="font-semibold">Должники</h2>
                    </div>

                    {debtors.length === 0 ? (
                        <div className="px-5 py-10 text-center text-sm text-gray-500">
                            Должников нет
                        </div>
                    ) : (
                        <ul className="divide-y divide-gray-100">
                            {debtors.map((debtor) => (
                                <li
                                    key={debtor.user_id}
                                    className="flex items-center justify-between gap-4 px-5 py-3"
                                >
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {debtor.tenant ?? '—'}
                                        </p>
                                        <p className="mt-0.5 text-xs text-gray-500">
                                            Просрочено начислений:{' '}
                                            {debtor.invoices_count}
                                        </p>
                                    </div>
                                    <span className="font-semibold text-red-600">
                                        {formatMoney(debtor.debt)}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

import { Head, usePage } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import renterRoutes from '@/routes/renter';
import type { RenterContract, RenterRentalRoom } from '@/types';
import { roomTypeLabels, formatRoomFloor } from '@/types';

type PageProps = {
    contract: RenterContract | null;
    room: RenterRentalRoom | null;
};

function formatDate(date: string | null): string {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function InfoItem({
    label,
    value,
}: {
    label: string;
    value: React.ReactNode;
}) {
    return (
        <div>
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="font-medium">{value}</dd>
        </div>
    );
}

export default function RenterContractPage() {
    const { contract, room } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Договор" />

            <div className="mb-6">
                <h1 className="mb-2 text-2xl font-semibold">Договор</h1>
                <p className="text-gray-500">
                    Информация об аренде и договоре
                </p>
            </div>

            <div className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Аренда</CardTitle>
                        <CardDescription>
                            Помещение, закреплённое за вами
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {room ? (
                            <dl className="grid gap-3 sm:grid-cols-3">
                                <InfoItem
                                    label="Тип"
                                    value={roomTypeLabels[room.type]}
                                />
                                <InfoItem
                                    label="Номер"
                                    value={room.number}
                                />
                                {room.floor !== null && (
                                    <InfoItem
                                        label="Этаж"
                                        value={formatRoomFloor(room.floor)}
                                    />
                                )}
                                <InfoItem
                                    label="Площадь"
                                    value={`${room.area.toFixed(2)} м²`}
                                />
                            </dl>
                        ) : (
                            <p className="text-muted-foreground">
                                Помещение не назначено
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Договор аренды</CardTitle>
                        <CardDescription>
                            Условия и документы по договору
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {contract ? (
                            <dl className="grid gap-3 sm:grid-cols-2">
                                <InfoItem
                                    label="Номер договора"
                                    value={contract.number}
                                />
                                <InfoItem
                                    label="Арендная плата"
                                    value={`${contract.monthly_rent.toFixed(2)} BYN`}
                                />
                                <InfoItem
                                    label="Дата начала"
                                    value={formatDate(contract.start_date)}
                                />
                                <InfoItem
                                    label="Дата окончания"
                                    value={formatDate(contract.end_date)}
                                />
                                <InfoItem
                                    label="Примечания"
                                    value={contract.notes || '—'}
                                />
                                <InfoItem
                                    label="Файл"
                                    value={
                                        contract.file_url ? (
                                            contract.is_image ? (
                                                <img
                                                    src={contract.file_url}
                                                    alt={
                                                        contract.file_name ??
                                                        'Договор'
                                                    }
                                                    className="mt-1 max-h-48 rounded-md border object-contain"
                                                />
                                            ) : (
                                                <a
                                                    href={contract.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex items-center gap-1 text-primary hover:underline"
                                                >
                                                    <FileText size={16} />
                                                    {contract.file_name ??
                                                        'Скачать файл'}
                                                </a>
                                            )
                                        ) : (
                                            '—'
                                        )
                                    }
                                />
                            </dl>
                        ) : (
                            <p className="text-muted-foreground">
                                Договор пока не оформлен
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

RenterContractPage.layout = {
    breadcrumbs: [
        {
            title: 'Договор',
            href: renterRoutes.contract(),
        },
    ],
};

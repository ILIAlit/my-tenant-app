import { Form, Head, Link, setLayoutProps, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import RenterProfileForm from '@/components/renters/settings/renter-profile-form';
import RenterInitialMeterReadingsForm from '@/components/renters/settings/renter-initial-meter-readings-form';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import renters from '@/routes/renters';
import type {
    AssignableRoom,
    RenterContract,
    RenterInitialMeterReadings,
    RenterServiceItem,
    User,
} from '@/types';
import { meterTypeLabels, roomNumberLabel, roomStatusLabels, roomWithFloorLabel } from '@/types';
import { renterFullName } from '@/utils/renter';

type PageProps = {
    renter: User;
    rooms: AssignableRoom[];
    contract: RenterContract | null;
    services: RenterServiceItem[];
    initialMeterReadings: RenterInitialMeterReadings;
};

type OpenForm = 'profile' | 'room' | 'contract' | 'service' | 'initialMeterReadings' | null;

function roomLabel(room: AssignableRoom): string {
    const status =
        room.status === 'repair'
            ? ` (${roomStatusLabels.repair})`
            : room.status === 'occupied' && room.user_id
              ? ' (занята)'
              : '';

    return `${roomWithFloorLabel(room)}${status}`;
}

function formatDate(date: string | null): string {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('ru-RU');
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

export default function RenterSettingsPage() {
    const { renter, rooms, contract, services, initialMeterReadings } =
        usePage<PageProps>().props;
    const [openForm, setOpenForm] = useState<OpenForm>(null);
    const currentRoomId = renter.room?.id?.toString() ?? '';

    const toggleForm = (form: Exclude<OpenForm, null>) => {
        setOpenForm((current) => (current === form ? null : form));
    };

    const closeForm = () => setOpenForm(null);

    setLayoutProps({
        breadcrumbs: [
            {
                title: 'Арендаторы',
                href: renters.get(),
            },
            {
                title: renterFullName(renter),
                href: renters.settings(renter.id),
            },
            {
                title: 'Настройка',
                href: renters.settings(renter.id),
            },
        ],
    });

    return (
        <>
            <Head title={`Настройка — ${renterFullName(renter)}`} />

            <div className="space-y-6">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>Арендатор</CardTitle>
                            <CardDescription>
                                Контактные данные и учётная запись
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => toggleForm('profile')}
                        >
                            <Pencil size={16} />
                            Редактировать
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <dl className="grid gap-3 sm:grid-cols-2">
                            <InfoItem
                                label="ФИО"
                                value={renterFullName(renter)}
                            />
                            <InfoItem label="Логин" value={renter.login} />
                            <InfoItem label="Почта" value={renter.email} />
                            <InfoItem
                                label="Телефон"
                                value={renter.phone || '—'}
                            />
                        </dl>

                        {openForm === 'profile' && (
                            <RenterProfileForm
                                renter={renter}
                                onCancel={closeForm}
                                onSuccess={closeForm}
                            />
                        )}
                    </CardContent>
                </Card>

              

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>Договор</CardTitle>
                            <CardDescription>
                                Договор аренды арендатора
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => toggleForm('contract')}
                        >
                            <Pencil size={16} />
                            {contract ? 'Редактировать' : 'Создать'}
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {contract ? (
                            <dl className="grid gap-3 sm:grid-cols-2">
                                <InfoItem
                                    label="Номер"
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
                                                    className="mt-1 max-h-32 rounded-md border object-contain"
                                                />
                                            ) : (
                                                <a
                                                    href={contract.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="text-primary hover:underline"
                                                >
                                                    {
                                                        'Открыть файл'}
                                                </a>
                                            )
                                        ) : (
                                            '—'
                                        )
                                    }
                                />
                            </dl>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Договор не создан
                            </p>
                        )}

                        {openForm === 'contract' && (
                            <Form
                                action={renters.contract.url(renter.id)}
                                method="put"
                                options={{
                                    preserveScroll: true,
                                    //forceFormData: true,
                                }}
                                className="space-y-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/50 p-4"
                                onSuccess={closeForm}
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="number">
                                                Номер договора *
                                            </Label>
                                            <Input
                                                id="number"
                                                name="number"
                                                defaultValue={
                                                    contract?.number ?? ''
                                                }
                                                required
                                                placeholder="ДГ-2026-001"
                                            />
                                            <InputError
                                                message={errors.number}
                                            />
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="start_date">
                                                    Дата начала *
                                                </Label>
                                                <Input
                                                    id="start_date"
                                                    name="start_date"
                                                    type="date"
                                                    defaultValue={
                                                        contract?.start_date ??
                                                        ''
                                                    }
                                                    required
                                                />
                                                <InputError
                                                    message={errors.start_date}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="end_date">
                                                    Дата окончания
                                                </Label>
                                                <Input
                                                    id="end_date"
                                                    name="end_date"
                                                    type="date"
                                                    defaultValue={
                                                        contract?.end_date ??
                                                        ''
                                                    }
                                                />
                                                <InputError
                                                    message={errors.end_date}
                                                />
                                            </div>
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="monthly_rent">
                                                Арендная плата (в месяц) *
                                            </Label>
                                            <Input
                                                id="monthly_rent"
                                                name="monthly_rent"
                                                type="number"
                                                min={0}
                                                step={0.01}
                                                defaultValue={
                                                    contract?.monthly_rent ?? ''
                                                }
                                                required
                                                placeholder="500.00"
                                            />
                                            <InputError
                                                message={errors.monthly_rent}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contract_notes">
                                                Примечания
                                            </Label>
                                            <textarea
                                                id="contract_notes"
                                                name="notes"
                                                rows={3}
                                                defaultValue={
                                                    contract?.notes ?? ''
                                                }
                                                placeholder="Дополнительные условия"
                                                className="block w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            />
                                            <InputError
                                                message={errors.notes}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contract_file">
                                                Файл договора
                                            </Label>
                                            <Input
                                                id="contract_file"
                                                name="file"
                                                type="file"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp,image/*,application/pdf"
                                            />
                                            <p className="text-xs text-muted-foreground">
                                                PDF или изображение, до 10 МБ
                                            </p>
                                            <InputError message={errors.file} />
                                        </div>

                                        {contract?.file_url && (
                                            <label className="flex items-center gap-2 text-sm">
                                                <input
                                                    type="checkbox"
                                                    name="remove_file"
                                                    value="1"
                                                    className="size-4 rounded border-input"
                                                />
                                                Удалить прикреплённый файл
                                            </label>
                                        )}

                                        <div className="flex gap-2">
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                {contract
                                                    ? 'Сохранить'
                                                    : 'Создать'}
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={closeForm}
                                            >
                                                Отмена
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>Начальные показания счётчиков</CardTitle>
                            <CardDescription>
                                Базовые показания при заселении арендатора
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => toggleForm('initialMeterReadings')}
                        >
                            <Pencil size={16} />
                            {Object.values(initialMeterReadings).some(Boolean)
                                ? 'Изменить'
                                : 'Задать'}
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {(
                                [
                                    'cold_water',
                                    'hot_water',
                                    'electricity',
                                ] as const
                            ).map((type) => {
                                const reading = initialMeterReadings[type];
                                const unit =
                                    type === 'electricity' ? 'кВт·ч' : 'м³';

                                return (
                                    <InfoItem
                                        key={type}
                                        label={meterTypeLabels[type]}
                                        value={
                                            reading
                                                ? `${reading.value.toFixed(3)} ${unit} · ${formatDate(reading.reading_date)}`
                                                : '—'
                                        }
                                    />
                                );
                            })}
                        </dl>

                        {openForm === 'initialMeterReadings' && (
                            <RenterInitialMeterReadingsForm
                                renterId={renter.id}
                                initialMeterReadings={initialMeterReadings}
                                onCancel={closeForm}
                                onSuccess={closeForm}
                            />
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>Услуги</CardTitle>
                            <CardDescription>
                                Услуги, назначенные арендатору
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => toggleForm('service')}
                        >
                            <Plus size={16} />
                            Добавить
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {services.length > 0 ? (
                            <div className="rounded-lg border border-gray-200">
                                <ul className="divide-y divide-gray-100">
                                    {services.map((service) => (
                                        <li
                                            key={service.id}
                                            className="flex items-center justify-between gap-4 px-4 py-3"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {service.name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {service.price.toFixed(2)}{' '}
                                                    BYN
                                                    {service.notes
                                                        ? ` · ${service.notes}`
                                                        : ''}
                                                </p>
                                            </div>
                                            <Link
                                                href={renters.services.destroy(
                                                    {
                                                        id: renter.id,
                                                        serviceId: service.id,
                                                    },
                                                )}
                                                as="button"
                                                method="delete"
                                                preserveScroll
                                                className="inline-flex size-9 items-center justify-center rounded-md hover:bg-red-50"
                                                aria-label="Удалить услугу"
                                            >
                                                <Trash2
                                                    size={18}
                                                    className="text-red-600"
                                                />
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Услуги не добавлены
                            </p>
                        )}

                        {openForm === 'service' && (
                            <Form
                                action={renters.services.store.url(renter.id)}
                                method="post"
                                options={{ preserveScroll: true }}
                                className="space-y-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/50 p-4"
                                resetOnSuccess
                                onSuccess={closeForm}
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="service_name">
                                                Название услуги *
                                            </Label>
                                            <Input
                                                id="service_name"
                                                name="name"
                                                required
                                                placeholder="Интернет, уборка..."
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="price">
                                                Цена *
                                            </Label>
                                            <Input
                                                id="price"
                                                name="price"
                                                type="number"
                                                min={0}
                                                step={0.01}
                                                required
                                                placeholder="25.00"
                                            />
                                            <InputError
                                                message={errors.price}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="service_notes">
                                                Примечания
                                            </Label>
                                            <textarea
                                                id="service_notes"
                                                name="notes"
                                                rows={2}
                                                placeholder="Описание услуги"
                                                className="block w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            />
                                            <InputError
                                                message={errors.notes}
                                            />
                                        </div>

                                        <div className="flex gap-2">
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                Добавить
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={closeForm}
                                            >
                                                Отмена
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        )}
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>Аренда</CardTitle>
                            <CardDescription>
                                Текущее назначение арендатора
                            </CardDescription>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => toggleForm('room')}
                        >
                            <Pencil size={16} />
                            {renter.room ? 'Изменить' : 'Назначить'}
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <dl className="grid gap-3 sm:grid-cols-2">
                            <InfoItem
                                label="Помещение"
                                value={
                                    renter.room
                                        ? roomWithFloorLabel(renter.room)
                                        : '—'
                                }
                            />
                        </dl>

                        {openForm === 'room' && (
                            <Form
                                action={renters.assignRoom.url(renter.id)}
                                method="put"
                                options={{ preserveScroll: true }}
                                className="space-y-4 rounded-lg border border-dashed border-gray-200 bg-gray-50/50 p-4"
                                onSuccess={closeForm}
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="room_id">
                                                Помещение
                                            </Label>
                                            <select
                                                id="room_id"
                                                name="room_id"
                                                defaultValue={currentRoomId}
                                                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            >
                                                <option value="">
                                                    Не назначена
                                                </option>
                                                {rooms.map((room) => (
                                                    <option
                                                        key={room.id}
                                                        value={room.id}
                                                        disabled={
                                                            room.status ===
                                                            'repair'
                                                        }
                                                    >
                                                        {roomLabel(room)}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError
                                                message={errors.room_id}
                                            />
                                        </div>

                                        <div className="flex gap-2">
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                Сохранить
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={closeForm}
                                            >
                                                Отмена
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

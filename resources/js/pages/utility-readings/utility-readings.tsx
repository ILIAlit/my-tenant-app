import { usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import UtilityReadingFormDialog from '@/components/utility-readings/utility-reading-form-dialog';
import UtilityReadingsHistory from '@/components/utility-readings/utility-readings-history';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import type { RoomUtilityData } from '@/types';

type PageProps = {
    roomsUtilityData: RoomUtilityData[];
};

export default function UtilityReadingsPage() {
    const { roomsUtilityData } = usePage<PageProps>().props;
    const [dialogOpen, setDialogOpen] = useState(false);

    const hasAvailablePeriods = useMemo(
        () =>
            roomsUtilityData.some(
                (room) => room.availablePeriods.length > 0,
            ),
        [roomsUtilityData],
    );

    return (
        <>
            <div className="flex items-center justify-between">
                <PageHeader
                    title="Показания счётчиков"
                    description="Коммунальные услуги: холодная и горячая вода, электроэнергия"
                />
                {roomsUtilityData.length > 0 && (
                    <Button
                        onClick={() => setDialogOpen(true)}
                        className="gap-2"
                        disabled={!hasAvailablePeriods}
                        title={
                            hasAvailablePeriods
                                ? undefined
                                : 'Нет открытых периодов для внесения показаний'
                        }
                    >
                        <Plus size={16} />
                        Ввести показания
                    </Button>
                )}
            </div>

            {roomsUtilityData.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                    У вас пока нет арендованных комнат
                </div>
            ) : (
                <>
                    {!hasAvailablePeriods && (
                        <p className="mb-4 text-sm text-gray-500">
                            Сейчас нет открытых периодов. Период считается от
                            даты заключения договора до того же числа
                            следующего месяца.
                        </p>
                    )}
                    <UtilityReadingsHistory
                        roomsUtilityData={roomsUtilityData}
                    />
                </>
            )}

            <UtilityReadingFormDialog
                open={dialogOpen}
                onOpenChange={setDialogOpen}
                roomsUtilityData={roomsUtilityData}
            />
        </>
    );
}

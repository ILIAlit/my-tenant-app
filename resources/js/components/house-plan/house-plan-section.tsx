import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import type { HousePlan, RoomPlanDisplayStatus } from '@/types';
import {
    roomPlanDisplayStatusLabels,
    roomPlanStatusBadge,
    roomPlanStatusCard,
    roomTypeLabels,
} from '@/types';

type HousePlanSectionProps = {
    housePlan: HousePlan;
    fitContent?: boolean;
    compact?: boolean;
};

type FloorTab = number | 'garages';

const legendStatuses: RoomPlanDisplayStatus[] = [
    'occupied',
    'free',
    'debt',
    'awaiting_payment',
    'repair',
];

export default function HousePlanSection({
    housePlan,
    fitContent = false,
    compact = false,
}: HousePlanSectionProps) {
    const garageRooms = useMemo(
        () => housePlan.rooms.filter((room) => room.floor === null),
        [housePlan.rooms],
    );
    const hasFloors = housePlan.floors.length > 0;
    const hasGarages = garageRooms.length > 0;

    const [selectedTab, setSelectedTab] = useState<FloorTab>(
        housePlan.floors[0] ?? 'garages',
    );

    const roomsOnTab = useMemo(() => {
        if (selectedTab === 'garages') {
            return garageRooms;
        }

        return housePlan.rooms.filter((room) => room.floor === selectedTab);
    }, [garageRooms, housePlan.rooms, selectedTab]);

    const tabStats = useMemo(() => {
        const total = roomsOnTab.length;
        const occupied = roomsOnTab.filter(
            (room) =>
                room.display_status === 'occupied' ||
                room.display_status === 'debt' ||
                room.display_status === 'awaiting_payment',
        ).length;
        const free = roomsOnTab.filter(
            (room) => room.display_status === 'free',
        ).length;
        const debt = roomsOnTab.filter(
            (room) => room.display_status === 'debt',
        ).length;

        return { total, occupied, free, debt };
    }, [roomsOnTab]);

    const emptyLabel =
        selectedTab === 'garages'
            ? 'Гаражей пока нет'
            : 'На этом этаже помещений нет';

    return (
        <DashboardSection
            title="План дома"
            fitContent={fitContent}
            compact={compact}
            className={compact ? 'h-full' : undefined}
            contentClassName={compact ? 'min-h-0 flex-1' : undefined}
            action={
                compact ? undefined : (
                <div className="hidden flex-wrap items-center gap-2 sm:flex">
                    {legendStatuses.map((status) => (
                        <div
                            key={status}
                            className="flex items-center gap-1 text-[10px]"
                        >
                            <span
                                className={cn(
                                    'inline-block size-2 rounded-full border',
                                    roomPlanStatusBadge[status],
                                )}
                            />
                            {roomPlanDisplayStatusLabels[status]}
                        </div>
                    ))}
                </div>
                )
            }
        >
            {!hasFloors && !hasGarages ? (
                <p className="text-muted-foreground text-sm">
                    Помещений пока нет
                </p>
            ) : (
                <>
                    <div className={cn('mb-2 flex shrink-0 flex-wrap gap-1', compact && 'mb-1')}>
                        {housePlan.floors.map((floor) => (
                            <Button
                                key={floor}
                                type="button"
                                variant={
                                    selectedTab === floor
                                        ? 'default'
                                        : 'outline'
                                }
                                size="sm"
                                className={cn(
                                    'h-7 px-2.5 text-xs',
                                    compact && 'h-6 px-2 text-[10px]',
                                )}
                                onClick={() => setSelectedTab(floor)}
                            >
                                Этаж {floor}
                            </Button>
                        ))}
                        {hasGarages && (
                            <Button
                                type="button"
                                variant={
                                    selectedTab === 'garages'
                                        ? 'default'
                                        : 'outline'
                                }
                                size="sm"
                                className={cn(
                                    'h-7 px-2.5 text-xs',
                                    compact && 'h-6 px-2 text-[10px]',
                                )}
                                onClick={() => setSelectedTab('garages')}
                            >
                                Гаражи
                            </Button>
                        )}
                    </div>

                    <DashboardSectionScroll fitContent={fitContent}>
                        {roomsOnTab.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                {emptyLabel}
                            </p>
                        ) : (
                            <div className="grid gap-1.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {roomsOnTab.map((room) => (
                                    <div
                                        key={room.id}
                                        className={cn(
                                            'rounded-md border',
                                            compact ? 'p-1.5' : 'p-2',
                                            roomPlanStatusCard[
                                                room.display_status
                                            ],
                                        )}
                                    >
                                        <div className="flex items-start justify-between gap-1">
                                            <div>
                                                <p
                                                    className={cn(
                                                        'font-semibold',
                                                        compact
                                                            ? 'text-xs'
                                                            : 'text-sm',
                                                    )}
                                                >
                                                    {room.type === 'garage'
                                                        ? roomTypeLabels.garage
                                                        : '№'}{' '}
                                                    {room.number}
                                                </p>
                                                <p className="text-muted-foreground text-[10px]">
                                                    {room.area} м²
                                                </p>
                                            </div>
                                            <span
                                                className={cn(
                                                    'inline-block shrink-0 rounded-full border px-1.5 py-0.5 text-[10px] font-medium',
                                                    roomPlanStatusBadge[
                                                        room.display_status
                                                    ],
                                                )}
                                            >
                                                {
                                                    roomPlanDisplayStatusLabels[
                                                        room.display_status
                                                    ]
                                                }
                                            </span>
                                        </div>
                                        {room.renter_name && (
                                            <p
                                                className={cn(
                                                    'mt-1 truncate',
                                                    compact
                                                        ? 'text-[10px]'
                                                        : 'text-xs',
                                                )}
                                            >
                                                {room.renter_name}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </DashboardSectionScroll>

                    <div
                        className={cn(
                            'text-muted-foreground mt-2 flex shrink-0 flex-wrap gap-x-3 gap-y-1 border-t pt-2 text-[10px] sm:text-xs',
                            compact && 'mt-1 pt-1',
                        )}
                    >
                        <span>
                            {selectedTab === 'garages'
                                ? `Всего гаражей: ${tabStats.total}`
                                : `Всего на этаже: ${tabStats.total}`}
                        </span>
                        <span>Занято: {tabStats.occupied}</span>
                        <span>Свободно: {tabStats.free}</span>
                        <span>Задолженность: {tabStats.debt}</span>
                    </div>
                </>
            )}
        </DashboardSection>
    );
}

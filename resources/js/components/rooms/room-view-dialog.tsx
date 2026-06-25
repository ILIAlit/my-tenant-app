import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Room } from '@/types';
import { roomNumberLabel, roomStatusLabels, roomTypeLabels, formatRoomFloor } from '@/types';

type Props = {
    room: Room | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleDateString('ru-RU', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
          })
        : '—';

export default function RoomViewDialog({ room, open, onOpenChange }: Props) {
    if (!room) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{roomNumberLabel(room)}</DialogTitle>
                </DialogHeader>

                <dl className="space-y-3 text-sm">
                    <div>
                        <dt className="text-muted-foreground">Тип</dt>
                        <dd className="font-medium">
                            {roomTypeLabels[room.type]}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Номер</dt>
                        <dd className="font-medium">{room.number}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Этаж</dt>
                        <dd>{formatRoomFloor(room.floor)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Площадь</dt>
                        <dd>{room.area} м²</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Статус</dt>
                        <dd>{roomStatusLabels[room.status]}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">
                            Дата последнего ремонта
                        </dt>
                        <dd>{formatDate(room.last_repair_date)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Примечания</dt>
                        <dd className="whitespace-pre-wrap">
                            {room.notes || '—'}
                        </dd>
                    </div>
                </dl>
            </DialogContent>
        </Dialog>
    );
}

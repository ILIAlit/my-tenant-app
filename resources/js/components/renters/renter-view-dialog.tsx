import { Link } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import renters from '@/routes/renters';
import type { User } from '@/types';
import { roomWithFloorLabel } from '@/types';
import { renterFullName } from '@/utils/renter';

type Props = {
    renter: User | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

function fullName(renter: User): string {
    return renterFullName(renter);
}

export default function RenterViewDialog({
    renter,
    open,
    onOpenChange,
}: Props) {
    if (!renter) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Арендатор</DialogTitle>
                </DialogHeader>

                <dl className="space-y-3 text-sm">
                    <div>
                        <dt className="text-muted-foreground">ФИО</dt>
                        <dd className="font-medium">{fullName(renter)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Логин</dt>
                        <dd>{renter.login}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Почта</dt>
                        <dd>{renter.email}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Телефон</dt>
                        <dd>{renter.phone || '—'}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Помещение</dt>
                        <dd>
                            {renter.room
                                ? roomWithFloorLabel(renter.room)
                                : '—'}
                        </dd>
                    </div>
                </dl>

                <Button asChild className="w-full">
                    <Link href={renters.settings(renter.id)}>Настройка</Link>
                </Button>
            </DialogContent>
        </Dialog>
    );
}

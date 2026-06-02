import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import type { News } from '@/types/news/news';

type ViewNewsModalProps = {
    news: News | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function ViewNewsModal({
    news,
    open,
    onOpenChange,
}: ViewNewsModalProps) {
    if (!news) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{news.title}</DialogTitle>
                </DialogHeader>

                <div className="grid gap-4">
                    <div className="text-sm text-gray-500">
                        Дата: {news.date}
                    </div>
                    <div className="whitespace-pre-wrap text-gray-700">
                        {news.text}
                    </div>
                </div>

                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Закрыть</Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

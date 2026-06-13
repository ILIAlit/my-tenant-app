import { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type ReceiptViewerProps = {
    url: string;
    title?: string;
};

export default function ReceiptViewer({
    url,
    title = 'Чек',
}: ReceiptViewerProps) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen(true)}
                className="group block overflow-hidden rounded-lg border border-gray-200 transition hover:border-primary"
                title="Открыть чек"
            >
                <img
                    src={url}
                    alt={title}
                    className="h-20 w-20 object-cover transition group-hover:scale-105"
                    loading="lazy"
                />
            </button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>{title}</DialogTitle>
                    </DialogHeader>
                    <div className="flex justify-center">
                        <img
                            src={url}
                            alt={title}
                            className="max-h-[70vh] w-auto rounded-lg"
                        />
                    </div>
                    <a
                        href={url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-center text-sm font-medium text-primary underline"
                    >
                        Открыть в новой вкладке
                    </a>
                </DialogContent>
            </Dialog>
        </>
    );
}

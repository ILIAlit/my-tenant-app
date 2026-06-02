import { Form } from '@inertiajs/react';
import AdminNewsController from '@/actions/App/Http/Controllers/Admin/News/AdminNewsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { News } from '@/types/news/news';

type UpdateNewsFormProps = {
    news: News;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function UpdateNewsForm({
    news,
    open,
    onOpenChange,
}: UpdateNewsFormProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Редактировать объявление</DialogTitle>
                </DialogHeader>

                <Form
                    {...AdminNewsController.updateNews.form(news.id)}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Заголовок</Label>

                                <Input
                                    id="title"
                                    className="mt-1 block w-full"
                                    name="title"
                                    defaultValue={news.title}
                                    required
                                    autoComplete="title"
                                    placeholder="заголовок"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.title}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="text">Текст</Label>

                                <Input
                                    id="text"
                                    type="text"
                                    className="mt-1 block w-full"
                                    name="text"
                                    defaultValue={news.text}
                                    required
                                    autoComplete="text"
                                    placeholder="текст"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.text}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="date">Дата</Label>

                                <Input
                                    id="date"
                                    className="mt-1 block w-full"
                                    type="date"
                                    name="date"
                                    defaultValue={news.date}
                                    autoComplete="date"
                                    placeholder="дата"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.date}
                                />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Закрыть
                                    </Button>
                                </DialogClose>
                                <Button disabled={processing} type="submit">
                                    Сохранить
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

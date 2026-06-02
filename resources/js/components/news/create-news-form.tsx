import { Form } from '@inertiajs/react';
import { useState } from 'react';
import AdminNewsController from '@/actions/App/Http/Controllers/Admin/News/AdminNewsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export default function CreateNewsForm() {
    const [hidden, setHidden] = useState(true);

    return hidden ? (
        <div className="mt-4 mb-4 rounded-xl border border-gray-200 bg-white p-6">
            <div className="flex items-center justify-between">
                <Button onClick={() => setHidden(false)} className="">
                    Создать объявление
                </Button>
            </div>
        </div>
    ) : (
        <div hidden={hidden} className="mt-4 mb-6 max-w-md">
            <Form
                {...AdminNewsController.createNews.form()}
                options={{
                    preserveScroll: true,
                }}
                className="space-y-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="name">Заголовок</Label>

                            <Input
                                id="title"
                                className="mt-1 block w-full"
                                name="title"
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
                            <Label htmlFor="email">Текст</Label>

                            <Input
                                id="text"
                                type="text"
                                className="mt-1 block w-full"
                                name="text"
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
                            <Label htmlFor="email">Дата</Label>

                            <Input
                                id="date"
                                className="mt-1 block w-full"
                                type="date"
                                name="date"
                                autoComplete="date"
                                placeholder="дата"
                            />

                            <InputError
                                className="mt-2"
                                message={errors.date}
                            />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button
                                disabled={processing}
                                data-test="update-profile-button"
                            >
                                Создать
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setHidden(true)}
                            >
                                Закрыть
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}

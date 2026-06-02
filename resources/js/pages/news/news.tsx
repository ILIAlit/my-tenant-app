import { Form, usePage } from '@inertiajs/react';
import { Edit, Eye, Trash2 } from 'lucide-react';
import AdminNewsController from '@/actions/App/Http/Controllers/Admin/News/AdminNewsController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type News = {
    id: number;
    title: string;
    text: string;
    date: string;
    user_id: number;
};

type PageProps = {
    news: News[];
};

export default function News() {
    const page = usePage<PageProps>();
    const { news } = page.props;

    return (
        <>
            <div className="p-4 lg:p-8">
                News
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
                            </div>
                        </>
                    )}
                </Form>
                <div className="mb-6 grid grid-cols-2 gap-6">
                    {news.map((announcement) => (
                        <div
                            key={announcement.id}
                            className="rounded-xl border border-gray-200 bg-white p-6 transition-shadow hover:shadow-lg"
                        >
                            <div className="mb-3 flex items-start justify-between">
                                <h3 className="text-lg font-semibold">
                                    {announcement.title}
                                </h3>
                                {}
                            </div>

                            <p className="mb-4 line-clamp-2 text-gray-600">
                                {announcement.text}
                            </p>

                            <div className="mb-4 flex items-center justify-between text-sm text-gray-500">
                                <span>{announcement.date}</span>
                                <span>{}</span>
                            </div>

                            <div className="flex gap-2">
                                <Button className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-50 py-2 text-sm text-blue-600 hover:bg-blue-100">
                                    <Eye size={16} />
                                    Просмотр
                                </Button>
                                <Button className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-50 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <Edit size={16} />
                                    Изменить
                                </Button>
                                <Button className="rounded-lg bg-red-50 px-4 py-2 text-red-600 hover:bg-red-100">
                                    <Trash2 size={16} />
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}

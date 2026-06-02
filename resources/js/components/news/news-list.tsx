import { Link, usePage } from '@inertiajs/react';
import { Edit, Eye, Trash2 } from 'lucide-react';
import { useState } from 'react';
import UpdateNewsForm from '@/components/news/update-news-form';
import ViewNewsModal from '@/components/news/view-news-modal';
import { Button } from '@/components/ui/button';
import { Role } from '@/enum/auth';
import news from '@/routes/news';
import type { Auth } from '@/types/auth';
import type { News } from '@/types/news/news';

type PageProps = {
    auth: Auth;
};

export default function NewsList({ newsItems }: { newsItems: News[] }) {
    const { user } = usePage<PageProps>().props.auth;

    const [selectedNews, setSelectedNews] = useState<News | null>(null);
    const [isUpdateModalOpen, setIsUpdateModalOpen] = useState(false);
    const [isViewModalOpen, setIsViewModalOpen] = useState(false);

    const handleEditClick = (newsItem: News) => {
        setSelectedNews(newsItem);
        setIsUpdateModalOpen(true);
    };

    const handleViewClick = (newsItem: News) => {
        setSelectedNews(newsItem);
        setIsViewModalOpen(true);
    };

    return (
        <>
            {newsItems.map((announcement) => (
                <div
                    key={announcement.id}
                    className="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-6 transition-shadow hover:shadow-lg"
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
                        <Button
                            onClick={() => handleViewClick(announcement)}
                            className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-50 py-2 text-sm text-blue-600 hover:bg-blue-100"
                        >
                            <Eye size={16} />
                            Просмотр
                        </Button>
                        {user.role === Role.Admin && (
                            <>
                                <Button
                                    onClick={() =>
                                        handleEditClick(announcement)
                                    }
                                    className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-50 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    <Edit size={16} />
                                    Изменить
                                </Button>
                                <Link
                                    href={news.delete(announcement.id)}
                                    as="button"
                                    className="rounded-lg bg-red-50 px-4 py-2 text-red-600 hover:bg-red-100"
                                >
                                    <Trash2 size={16} />
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            ))}

            {selectedNews && (
                <>
                    <UpdateNewsForm
                        news={selectedNews}
                        open={isUpdateModalOpen}
                        onOpenChange={setIsUpdateModalOpen}
                    />
                    <ViewNewsModal
                        news={selectedNews}
                        open={isViewModalOpen}
                        onOpenChange={setIsViewModalOpen}
                    />
                </>
            )}
        </>
    );
}

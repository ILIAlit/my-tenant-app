import { usePage, Head } from '@inertiajs/react';
import CreateNewsForm from '@/components/news/create-news-form';
import NewsList from '@/components/news/news-list';
import PageHeader from '@/components/ui/page-header';
import { Role } from '@/enum/auth';
import news from '@/routes/news';
import type { Auth } from '@/types/auth';
import type { News } from '@/types/news/news';

type PageProps = {
    news: News[];
    auth: Auth;
};

export default function News() {
    const page = usePage<PageProps>();
    const { news: newsItems } = page.props;
    const { user } = page.props.auth;

    return (
        <>
            <Head title="Объявления" />

            <PageHeader
                title="Объявления"
                description="Важные сообщения и уведомления"
            />
            {user.role === Role.Admin && <CreateNewsForm />}
            <div className="mt-6 mb-6 grid grid-cols-2 gap-6">
                <NewsList newsItems={newsItems} />
            </div>
        </>
    );
}

News.layout = {
    breadcrumbs: [
        {
            title: 'Объявления',
            href: news.get(),
        },
    ],
};

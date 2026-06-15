import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/ui/page-header';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import rooms from '@/routes/rooms';
import {} from '@/routes/security';
import type { NavItem, Rooms } from '@/types';

type PageProps = {
    room: Rooms;
};

export default function RoomsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    const page = usePage<PageProps>();
    const { room } = page.props;
    const roomId = room.id;

    const sidebarNavItems: NavItem[] = [
        {
            title: 'Изменить данные',
            href: rooms.getUpdate(roomId),
            icon: null,
        },
        {
            title: 'Добавить арендатора',
            href: rooms.getAddRenterToRoom(roomId),
            icon: null,
        },
        {
            title: 'Добавить услуги',
            href: editAppearance(),
            icon: null,
        },
    ];

    return (
        <>
            <PageHeader
                title="Настройки комнаты"
                description="Управление информацией о комнате и её арендаторах."
            />
            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1 space-x-0"
                        aria-label="Settings"
                    >
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': isCurrentOrParentUrl(item.href),
                                })}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </>
    );
}

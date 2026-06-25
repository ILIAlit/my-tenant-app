import { Link } from '@inertiajs/react';
import {
    Building2,
    CreditCard,
    Gauge,
    MessageSquare,
    Receipt,
    UserPlus,
} from 'lucide-react';
import type { ComponentType } from 'react';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import charges from '@/routes/charges';
import expenses from '@/routes/expenses';
import meterReadings from '@/routes/meter-readings';
import news from '@/routes/news';
import renters from '@/routes/renters';
import rooms from '@/routes/rooms';

type QuickAction = {
    title: string;
    shortTitle: string;
    href: string;
    icon: ComponentType<{ className?: string }>;
};

const quickActions: QuickAction[] = [
    {
        title: 'Создать помещение',
        shortTitle: 'Помещение',
        href: rooms.get({ query: { create: '1' } }).url,
        icon: Building2,
    },
    {
        title: 'Создать арендатора',
        shortTitle: 'Арендатор',
        href: renters.get({ query: { create: '1' } }).url,
        icon: UserPlus,
    },
    {
        title: 'Создать начисление',
        shortTitle: 'Начисление',
        href: charges.get({ query: { create: '1' } }).url,
        icon: CreditCard,
    },
    {
        title: 'Добавить расход',
        shortTitle: 'Расход',
        href: expenses.get({ query: { create: '1' } }).url,
        icon: Receipt,
    },
    {
        title: 'Ввести показания',
        shortTitle: 'Показания',
        href: meterReadings.get({ query: { create: '1' } }).url,
        icon: Gauge,
    },
    {
        title: 'Создать объявление',
        shortTitle: 'Объявление',
        href: news.get().url,
        icon: MessageSquare,
    },
];

type QuickActionsSectionProps = {
    compact?: boolean;
};

export default function QuickActionsSection({
    compact = false,
}: QuickActionsSectionProps) {
    return (
        <DashboardSection
            title="Быстрые действия"
            description={compact ? undefined : 'Частые операции'}
            fitContent={compact}
        >
            {compact ? (
                <div className="grid grid-cols-3 gap-1.5">
                    {quickActions.map((action) => (
                        <Link
                            key={action.title}
                            href={action.href}
                            title={action.title}
                            className="hover:bg-muted flex flex-col items-center gap-1 rounded-md border px-1 py-2 text-center text-[10px] transition-colors"
                        >
                            <div className="bg-primary/10 text-primary flex size-7 shrink-0 items-center justify-center rounded-md">
                                <action.icon className="size-3.5" />
                            </div>
                            <span className="font-medium leading-tight">
                                {action.shortTitle}
                            </span>
                        </Link>
                    ))}
                </div>
            ) : (
                <DashboardSectionScroll>
                    <div className="grid gap-1.5 sm:grid-cols-2">
                        {quickActions.map((action) => (
                            <Link
                                key={action.title}
                                href={action.href}
                                className="hover:bg-muted flex items-center gap-2 rounded-md border px-2 py-2 text-xs transition-colors sm:text-sm"
                            >
                                <div className="bg-primary/10 text-primary flex size-7 shrink-0 items-center justify-center rounded-md">
                                    <action.icon className="size-3.5" />
                                </div>
                                <span className="font-medium">
                                    {action.title}
                                </span>
                            </Link>
                        ))}
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}

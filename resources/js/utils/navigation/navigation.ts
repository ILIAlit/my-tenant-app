import {
    Home,
    Building2,
    Users,
    FileText,
    // CreditCard,
    // Activity,
    // DollarSign,
    // BarChart3,
    // Settings,
    MessageSquare,
    // ArrowLeftRight,
} from 'lucide-react';
import { dashboard } from '@/routes';
import news from '@/routes/news';
import renters from '@/routes/renters';
import rooms from '@/routes/rooms';
import type { NavItem } from '@/types';

export const mainAdminNavItems: NavItem[] = [
    {
        title: 'Главная',
        href: dashboard(),
        icon: Home,
    },
    {
        title: 'Комнаты',
        href: rooms.get(),
        icon: Building2,
    },
    {
        title: 'Гаражи',
        href: dashboard(),
        icon: Building2,
    },
    {
        title: 'Арендаторы',
        href: renters.get(),
        icon: Users,
    },
    {
        title: 'Договоры',
        href: dashboard(),
        icon: FileText,
    },
    {
        title: 'Объявления',
        href: news.get(),
        icon: MessageSquare,
    },
];

export const mainRenterNavItems: NavItem[] = [
    {
        title: 'Главная',
        href: dashboard(),
        icon: Home,
    },
    {
        title: 'Объявления',
        href: news.get(),
        icon: MessageSquare,
    },
];

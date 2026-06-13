import {
    Home,
    Building2,
    Users,
    FileText,
    ScrollText,
    CircleDollarSign,
    CreditCard,
    Gauge,
    Bell,
    //Activity,
    // DollarSign,
    // BarChart3,
    // Settings,
    MessageSquare,
    // ArrowLeftRight,
} from 'lucide-react';
import NotificationsController from '@/actions/App/Http/Controllers/Notifications/NotificationsController';
import { dashboard } from '@/routes';
import contracts from '@/routes/contracts';
import invoices from '@/routes/invoices';
import payments from '@/routes/payments';
import utilityReadings from '@/routes/utility-readings';
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
        href: contracts.allGet(),
        icon: ScrollText,
    },
    {
        title: 'Начисления',
        href: invoices.adminGet(),
        icon: FileText,
    },
    {
        title: 'Платежи',
        href: payments.adminGet(),
        icon: CreditCard,
    },
    {
        title: 'Показания',
        href: utilityReadings.allGet(),
        icon: Gauge,
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
        title: 'Аренда',
        href: rooms.getRenterRooms(),
        icon: CircleDollarSign,
    },
    {
        title: 'Начисления',
        href: invoices.get(),
        icon: FileText,
    },
    {
        title: 'Платежи',
        href: payments.get(),
        icon: CreditCard,
    },
    {
        title: 'Договоры',
        href: contracts.get(),
        icon: ScrollText,
    },
    {
        title: 'Показания',
        href: utilityReadings.get(),
        icon: Gauge,
    },
    {
        title: 'Уведомления',
        href: NotificationsController.index(),
        icon: Bell,
    },
    {
        title: 'Объявления',
        href: news.get(),
        icon: MessageSquare,
    },
];

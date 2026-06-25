import {
    Home,
    Building2,
    Users,
    FileText,
    CreditCard,
    Wallet,
    MessageSquare,
    Gauge,
    Receipt,
} from 'lucide-react';
import { dashboard } from '@/routes';
import news from '@/routes/news';
import renters from '@/routes/renters';
import rooms from '@/routes/rooms';
import contracts from '@/routes/contracts';
import charges from '@/routes/charges';
import payments from '@/routes/payments';
import expenses from '@/routes/expenses';
import meterReadings from '@/routes/meter-readings';
import renterRoutes from '@/routes/renter';
import type { NavItem } from '@/types';

export const mainAdminNavItems: NavItem[] = [
    {
        title: 'Главная',
        href: dashboard(),
        icon: Home,
    },
    {
        title: 'Комнаты и гаражи',
        href: rooms.get(),
        icon: Building2,
    },
    {
        title: 'Арендаторы',
        href: renters.get(),
        icon: Users,
    },
    {
        title: 'Договоры',
        href: contracts.get(),
        icon: FileText,
    },
    {
        title: 'Начисления',
        href: charges.get(),
        icon: CreditCard,
    },
    {
        title: 'Платежи',
        href: payments.get(),
        icon: Wallet,
    },
    {
        title: 'Расходы',
        href: expenses.get(),
        icon: Receipt,
    },
    {
        title: 'Показания',
        href: meterReadings.get(),
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
        title: 'Договор',
        href: renterRoutes.contract(),
        icon: FileText,
    },
    {
        title: 'Начисления',
        href: renterRoutes.charges(),
        icon: CreditCard,
    },
    {
        title: 'Платежи',
        href: renterRoutes.payments(),
        icon: Wallet,
    },
    {
        title: 'Показания',
        href: renterRoutes.meterReadings(),
        icon: Gauge,
    },
    {
        title: 'Объявления',
        href: news.get(),
        icon: MessageSquare,
    },
];

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
    // MessageSquare,
    // ArrowLeftRight,
} from 'lucide-react';
import { dashboard } from '@/routes';
import renters from '@/routes/renters';
import type { NavItem } from '@/types';

export const mainAdminNavItems: NavItem[] = [
    {
        title: 'Главная',
        href: dashboard(),
        icon: Home,
    },
    {
        title: 'Квартиры',
        href: dashboard(),
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
];

export const mainRenterNavItems: NavItem[] = [
    {
        title: 'Главная',
        href: dashboard(),
        icon: Home,
    },
];

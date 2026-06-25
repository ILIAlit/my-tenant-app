import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

type DashboardSectionProps = {
    title: string;
    description?: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
    fitContent?: boolean;
    compact?: boolean;
};

export function DashboardSection({
    title,
    description,
    action,
    children,
    className,
    contentClassName,
    fitContent = false,
    compact = false,
}: DashboardSectionProps) {
    return (
        <Card
            className={cn(
                'flex flex-col gap-0 py-0 shadow-sm',
                fitContent ? 'h-auto' : 'h-full min-h-0',
                className,
            )}
        >
            <CardHeader
                className={cn(
                    'flex shrink-0 flex-row items-start justify-between gap-2 space-y-0 px-3 sm:px-4',
                    compact ? 'py-1.5' : 'py-2',
                )}
            >
                <div className="min-w-0">
                    <CardTitle className={compact ? 'text-xs' : 'text-sm'}>
                        {title}
                    </CardTitle>
                    {description && (
                        <CardDescription className="line-clamp-1 text-[10px] sm:text-xs">
                            {description}
                        </CardDescription>
                    )}
                </div>
                {action}
            </CardHeader>
            <CardContent
                className={cn(
                    'flex flex-col pt-0 sm:px-4',
                    compact ? 'px-2 pb-2' : 'px-3 pb-3',
                    fitContent ? '' : 'min-h-0 flex-1',
                    contentClassName,
                )}
            >
                {children}
            </CardContent>
        </Card>
    );
}

export function DashboardSectionScroll({
    children,
    className,
    fitContent = false,
}: {
    children: ReactNode;
    className?: string;
    fitContent?: boolean;
}) {
    return (
        <div
            className={cn(
                fitContent ? '' : 'min-h-0 flex-1 overflow-y-auto',
                'pr-1',
                className,
            )}
        >
            {children}
        </div>
    );
}

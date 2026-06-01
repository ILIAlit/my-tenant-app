import { Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import renters from '@/routes/renters';
//import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="p-8">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="mb-2 text-2xl font-semibold">
                            Арендаторы
                        </h1>
                        <p className="text-gray-400">
                            Управление арендаторами и контактами
                        </p>
                    </div>
                    <Button>
                        <Plus size={20} />
                        Добавить арендатора
                    </Button>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Арендаторы',
            href: renters.get(),
        },
    ],
};

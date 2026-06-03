


export default function PageHeader({title, description}: {title: string, description: string}) {
    return (
        <div className="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="mb-2 text-2xl font-semibold">{title}</h1>
                        <p className="text-gray-400">
                            {description}
                        </p>
                    </div>
                </div>
    )
} 
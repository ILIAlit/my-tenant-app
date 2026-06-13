<?php

namespace App\Console\Commands;

use App\Actions\Invoices\MonthlyInvoiceGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate
                            {--date= : Дата, на которую формировать начисления (Y-m-d)}
                            {--dry-run : Показать, сколько начислений будет создано, без записи в БД}';

    protected $description = 'Автоматически создаёт ежемесячные начисления по договорам аренды';

    public function handle(MonthlyInvoiceGenerator $generator): int
    {
        $dateOption = $this->option('date');
        $asOf = $dateOption !== null && $dateOption !== ''
            ? CarbonImmutable::parse((string) $dateOption)->startOfDay()
            : CarbonImmutable::now()->startOfDay();

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Режим проверки: записи в БД не создаются.');
        }

        $this->info('Формирование начислений на '.$asOf->format('d.m.Y').'...');

        $result = $generator->generate($asOf, $dryRun);

        $this->info("Создано: {$result['created']}, пропущено: {$result['skipped']}.");

        return self::SUCCESS;
    }
}

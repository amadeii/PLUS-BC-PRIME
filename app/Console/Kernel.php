<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('alerta:cron')->dailyAt(1,13);
        $schedule->command('alerta:cron')->everyMinute();
        $schedule->command('cash-back:cron')->dailyAt('08:00');
        $schedule->command('app:boleto-cron')->monthly();
        $schedule->command('aniversario:cron')->hourly();

        $schedule->command('fila-envio:cron')->dailyAt('08:00');
        $schedule->command('reativar:cron')->dailyAt('09:00');
        
        $schedule->command('boletos:verificar-sicredi')
        ->hourlyAt(0)
        ->withoutOverlapping();

        $schedule->command('boletos:verificar-asaas')
        ->hourlyAt(10)
        ->withoutOverlapping();

        $schedule->command('nuvemshop:pedidos')->everyThreeMinutes();

        $schedule->command('recorrencias:gerar-cobrancas')
        ->dailyAt('07:00')
        ->withoutOverlapping();

        $schedule->command('recorrencias:enviar-comunicacoes')
        ->dailyAt('08:00')
        ->withoutOverlapping();

        $schedule->command('recorrencia:processar-regra')
        ->hourly()
        ->withoutOverlapping();
        // $schedule->command('nuvemshop:pedidos')->everyTenMinutes();

        $schedule->command('xml-contador:enviar')
        ->dailyAt('08:00')
        ->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

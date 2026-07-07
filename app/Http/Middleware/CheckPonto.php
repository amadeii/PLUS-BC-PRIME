<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Funcionario;
use App\Models\PontoConfiguracao;
use App\Models\PontoFuncionario;
use App\Models\PontoRegistro;
use Carbon\Carbon;

class CheckPonto
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user->admin) {
            return $next($request);
        }

        if (
            $request->routeIs('meu-ponto.index') ||
            $request->routeIs('meu-ponto.bater') ||
            $request->is('logout')
        ) {
            return $next($request);
        }

        $funcionario = Funcionario::where('usuario_id', $user->id)
            ->where('status', 1)
            ->first();

        if (!$funcionario || (int) $funcionario->bater_ponto !== 1) {
            return $next($request);
        }

        $config = PontoConfiguracao::where('empresa_id', $funcionario->empresa_id)->first();

        if (!$config) {
            return $next($request);
        }

        $hoje = Carbon::today();

        $pontoFuncionario = PontoFuncionario::with('jornada.dias')
            ->where('empresa_id', $funcionario->empresa_id)
            ->where('funcionario_id', $funcionario->id)
            ->whereDate('data_inicio', '<=', $hoje->toDateString())
            ->where(function ($q) use ($hoje) {
                $q->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $hoje->toDateString());
            })
            ->first();

        if (
            !$pontoFuncionario ||
            !$pontoFuncionario->jornada ||
            !$pontoFuncionario->jornada->ativo
        ) {
            return $next($request);
        }

        $diaSemana = $hoje->dayOfWeek;

        $diaJornada = $pontoFuncionario->jornada->dias
            ->where('dia_semana', $diaSemana)
            ->first();

        // Se hoje não tem jornada, libera acesso
        if (!$diaJornada || !$diaJornada->entrada || !$diaJornada->saida) {
            return $next($request);
        }

        $ultimoRegistroHoje = PontoRegistro::where('empresa_id', $funcionario->empresa_id)
            ->where('funcionario_id', $funcionario->id)
            ->whereDate('data_hora', $hoje->toDateString())
            ->orderBy('data_hora', 'desc')
            ->first();

        // Se tem jornada hoje e ainda não bateu ponto, força ir para Meu Ponto
        if (!$ultimoRegistroHoje) {
            return redirect()->route('meu-ponto.index');
        }

        // Se já bateu alguma marcação hoje, libera o sistema
        if (in_array($ultimoRegistroHoje->tipo, [
            'entrada',
            'saida',
            'intervalo_inicio',
            'intervalo_fim'
        ])) {
            return $next($request);
        }

        return redirect()->route('meu-ponto.index');
    }
}
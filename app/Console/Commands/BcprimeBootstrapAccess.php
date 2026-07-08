<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\Localizacao;
use App\Models\Permission;
use App\Models\Plano;
use App\Models\PlanoEmpresa;
use App\Models\Role;
use App\Models\User;
use App\Models\UsuarioEmpresa;
use App\Models\UsuarioLocalizacao;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class BcprimeBootstrapAccess extends Command
{
    protected $signature = 'bcprime:bootstrap-access';

    protected $description = 'Prepare initial BCPrime access records without changing visual customizations.';

    private const BOOTSTRAP_CNPJ = '00000000000000';
    private const BOOTSTRAP_COMPANY_NAME = 'BCPrime';
    private const BOOTSTRAP_PLAN_NAME = 'BCPrime Completo';
    private const BOOTSTRAP_LOCATION = 'BL0001';
    private const BOOTSTRAP_PASSWORD = '@Ometas2026#';

    public function handle(): int
    {
        $missingSchema = $this->missingRequiredSchema();

        if ($missingSchema !== []) {
            foreach ($missingSchema as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $result = DB::transaction(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $permissionResult = $this->syncDefaultPermissions();
            $roleResult = $this->syncPlatformRoles();
            $empresa = $this->ensureEmpresa();
            $companyRole = $this->ensureCompanyAdminRole($empresa);
            $plano = $this->ensurePlano();
            $planoEmpresa = $this->ensurePlanoEmpresa($empresa, $plano);
            $localizacao = $this->ensureLocalizacao($empresa);
            $userResult = $this->ensureBootstrapUsers($empresa, $localizacao, $companyRole);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return [
                'permissions' => $permissionResult,
                'roles' => $roleResult,
                'company_role' => $companyRole,
                'empresa' => $empresa,
                'plano' => $plano,
                'plano_empresa' => $planoEmpresa,
                'localizacao' => $localizacao,
                'users' => $userResult,
            ];
        });

        $this->info('BCPrime bootstrap access finished.');
        $this->line('Permissions created: ' . $result['permissions']['created']);
        $this->line('Permissions updated/kept: ' . $result['permissions']['kept']);
        $this->line('Roles synced: ' . implode(', ', $result['roles']));
        $this->line('Company role: #' . $result['company_role']->id . ' - ' . $result['company_role']->name);
        $this->line('Empresa: #' . $result['empresa']->id . ' - ' . $result['empresa']->nome);
        $this->line('Plano: #' . $result['plano']->id . ' - ' . $result['plano']->nome);
        $this->line('Plano empresa: #' . $result['plano_empresa']->id);
        $this->line('Localizacao: #' . $result['localizacao']->id . ' - ' . $result['localizacao']->descricao);

        foreach ($result['users'] as $email => $status) {
            $this->line($email . ': ' . $status);
        }

        return self::SUCCESS;
    }

    private function syncDefaultPermissions(): array
    {
        $created = 0;
        $kept = 0;

        foreach (array_merge(Permission::defaultPermissions(), $this->extraRequiredPermissions()) as $data) {
            $permission = Permission::updateOrCreate(
                [
                    'name' => $data['name'],
                    'guard_name' => 'web',
                ],
                [
                    'description' => $data['description'] ?? $data['name'],
                ]
            );

            $permission->wasRecentlyCreated ? $created++ : $kept++;
        }

        return compact('created', 'kept');
    }

    private function syncPlatformRoles(): array
    {
        $roles = [
            [
                'name' => 'gestor_plataforma',
                'description' => 'Gestor Plataforma',
                'type_user' => 1,
            ],
            [
                'name' => 'admin',
                'description' => 'Admin',
                'type_user' => 2,
            ],
        ];

        $permissions = Permission::where('guard_name', 'web')->get();
        $synced = [];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                [
                    'name' => $roleData['name'],
                    'guard_name' => 'web',
                ],
                [
                    'description' => $roleData['description'],
                    'empresa_id' => null,
                    'is_default' => 0,
                    'type_user' => $roleData['type_user'],
                ]
            );

            $role->syncPermissions($permissions);
            $synced[] = $role->name;
        }

        return $synced;
    }

    private function ensureEmpresa(): Empresa
    {
        $empresa = Empresa::where('cpf_cnpj', self::BOOTSTRAP_CNPJ)->first();

        if ($empresa) {
            return $empresa;
        }

        if (Empresa::count() > 0) {
            return Empresa::orderBy('id')->first();
        }

        return Empresa::create([
            'nome' => self::BOOTSTRAP_COMPANY_NAME,
            'nome_fantasia' => self::BOOTSTRAP_COMPANY_NAME,
            'cpf_cnpj' => self::BOOTSTRAP_CNPJ,
            'email' => 'admin@ometas.com.br',
            'celular' => '00000000000',
            'status' => 1,
            'cep' => '00000-000',
            'rua' => 'A definir',
            'numero' => 'S/N',
            'bairro' => 'Centro',
            'cidade_id' => null,
            'ambiente' => 2,
            'tributacao' => 'Simples Nacional',
            'observacao_padrao_nfe' => '',
            'observacao_padrao_nfce' => '',
            'mensagem_aproveitamento_credito' => '',
            'logo' => '',
            'receber_com_boleto' => 0,
            'cadastrar_planos' => 0,
            'limite_cadastro_empresas' => 0,
        ]);
    }

    private function ensureCompanyAdminRole(Empresa $empresa): Role
    {
        $role = Role::updateOrCreate(
            [
                'name' => 'Admin#' . $empresa->id,
                'guard_name' => 'web',
            ],
            [
                'description' => 'Admin',
                'empresa_id' => $empresa->id,
                'is_default' => 1,
                'type_user' => 2,
            ]
        );

        $role->syncPermissions(Permission::where('guard_name', 'web')->get());

        return $role;
    }

    private function ensurePlano(): Plano
    {
        return Plano::updateOrCreate(
            ['nome' => self::BOOTSTRAP_PLAN_NAME],
            [
                'descricao' => 'Plano base para bootstrap inicial BCPrime.',
                'descricao_curta' => 'Bootstrap BCPrime',
                'maximo_nfes' => 999999,
                'maximo_nfces' => 999999,
                'maximo_ctes' => 999999,
                'maximo_mdfes' => 999999,
                'maximo_usuarios' => 999,
                'maximo_locais' => 999,
                'imagem' => '',
                'visivel_clientes' => 0,
                'visivel_contadores' => 0,
                'status' => 1,
                'valor' => 0,
                'valor_implantacao' => 0,
                'intervalo_dias' => 3650,
                'dias_teste' => null,
                'modulos' => json_encode($this->allPlanModules(), JSON_UNESCAPED_UNICODE),
                'auto_cadastro' => 0,
                'fiscal' => 1,
                'segmento_id' => null,
                'contador_id' => null,
            ]
        );
    }

    private function ensurePlanoEmpresa(Empresa $empresa, Plano $plano): PlanoEmpresa
    {
        $planoEmpresa = PlanoEmpresa::firstOrNew([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
        ]);

        if (!$planoEmpresa->exists || Carbon::parse($planoEmpresa->data_expiracao)->isPast()) {
            $planoEmpresa->fill([
                'data_expiracao' => Carbon::now()->addYears(10)->toDateString(),
                'valor' => 0,
                'forma_pagamento' => 'Bootstrap',
                'contador_id' => null,
            ]);
            $planoEmpresa->save();
        }

        return $planoEmpresa;
    }

    private function ensureLocalizacao(Empresa $empresa): Localizacao
    {
        return Localizacao::updateOrCreate(
            [
                'empresa_id' => $empresa->id,
                'descricao' => self::BOOTSTRAP_LOCATION,
            ],
            [
                'status' => 1,
                'nome' => $empresa->nome,
                'nome_fantasia' => $empresa->nome_fantasia,
                'cpf_cnpj' => $empresa->cpf_cnpj,
                'email' => $empresa->email,
                'celular' => $empresa->celular,
                'cep' => $empresa->cep,
                'rua' => $empresa->rua,
                'numero' => $empresa->numero,
                'bairro' => $empresa->bairro,
                'cidade_id' => $empresa->cidade_id,
                'ambiente' => $empresa->ambiente,
                'tributacao' => $empresa->tributacao,
                'logo' => $empresa->logo ?? '',
                'mensagem_aproveitamento_credito' => $empresa->mensagem_aproveitamento_credito ?? '',
                'substituto_tributario' => $empresa->substituto_tributario ?? 0,
            ]
        );
    }

    private function ensureBootstrapUsers(Empresa $empresa, Localizacao $localizacao, Role $companyRole): array
    {
        $users = [
            'master@ometas.com.br' => [
                'name' => 'Master Ometas',
                'role' => 'gestor_plataforma',
            ],
            'admin@ometas.com.br' => [
                'name' => 'Admin Ometas',
                'role' => 'admin',
            ],
        ];

        $result = [];

        foreach ($users as $email => $data) {
            $user = User::firstOrNew(['email' => $email]);
            $created = !$user->exists;

            $user->forceFill([
                'name' => $data['name'],
                'password' => Hash::make(self::BOOTSTRAP_PASSWORD),
                'admin' => 1,
                'status' => 1,
            ])->save();

            UsuarioEmpresa::firstOrCreate([
                'empresa_id' => $empresa->id,
                'usuario_id' => $user->id,
            ]);

            UsuarioLocalizacao::firstOrCreate([
                'usuario_id' => $user->id,
                'localizacao_id' => $localizacao->id,
            ]);

            if (!$user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }

            if (!$user->hasRole($companyRole->name)) {
                $user->assignRole($companyRole->name);
            }

            $result[$email] = ($created ? 'criado' : 'atualizado') . ', ativo, vinculado e com roles ' . $data['role'] . ', ' . $companyRole->name;
        }

        return $result;
    }

    private function extraRequiredPermissions(): array
    {
        return [
            [
                'name' => 'recorrencia_cobranca_create',
                'description' => 'Cria cobranca recorrente',
            ],
            [
                'name' => 'recorrencia_contrato_create',
                'description' => 'Cria contrato recorrente',
            ],
            [
                'name' => 'recorrencia_contrato_edit',
                'description' => 'Edita contrato recorrente',
            ],
            [
                'name' => 'recorrencia_contrato_delete',
                'description' => 'Deleta contrato recorrente',
            ],
        ];
    }

    private function missingRequiredSchema(): array
    {
        $requirements = [
            'permissions' => ['name', 'description', 'guard_name'],
            'roles' => ['name', 'description', 'guard_name', 'empresa_id', 'is_default', 'type_user'],
            'empresas' => ['nome', 'nome_fantasia', 'cpf_cnpj', 'status', 'ambiente', 'tributacao', 'observacao_padrao_nfe', 'observacao_padrao_nfce', 'mensagem_aproveitamento_credito'],
            'planos' => ['nome', 'descricao', 'maximo_nfes', 'maximo_nfces', 'maximo_ctes', 'maximo_mdfes', 'maximo_usuarios', 'maximo_locais', 'imagem', 'status', 'valor', 'intervalo_dias', 'modulos', 'auto_cadastro', 'fiscal'],
            'plano_empresas' => ['empresa_id', 'plano_id', 'data_expiracao', 'valor', 'forma_pagamento'],
            'localizacaos' => ['empresa_id', 'descricao', 'status', 'nome', 'cpf_cnpj', 'ambiente', 'tributacao', 'mensagem_aproveitamento_credito'],
            'usuario_empresas' => ['empresa_id', 'usuario_id'],
            'usuario_localizacaos' => ['usuario_id', 'localizacao_id'],
        ];

        $missing = [];

        foreach ($requirements as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $missing[] = "Tabela ausente: {$table}";
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $missing[] = "Coluna ausente: {$table}.{$column}";
                }
            }
        }

        return $missing;
    }

    private function allPlanModules(): array
    {
        return [
            'Agendamentos',
            'Atendimento',
            'Cardapio',
            'Compras',
            'Controle de Fretes',
            'CRM',
            'CTe',
            'Delivery',
            'Ecommerce',
            'Financeiro',
            'Gestão de Produção',
            'IFood',
            'Localizações',
            'MDFe',
            'Mercado Livre',
            'NFCe',
            'NFSe',
            'Nuvem Shop',
            'Ordem de Produção',
            'PDV',
            'Pessoas',
            'Planejamento de Custos',
            'Pré venda',
            'Produtos',
            'Reservas',
            'Serviços',
            'Sped',
            'Usuários',
            'Veiculos',
            'Vendas',
            'VendiZap',
            'Woocommerce',
        ];
    }
}

# Matriz de Permissões

A tabela abaixo descreve as ações suportadas pelo portal e quais perfis podem executá-las. Os perfis são alinhados com as políticas implementadas em `App\Auth\Policies\PermissionMatrix`.

| Ação | Descrição | Administrador | Escola | Professor | Aluno |
| --- | --- | :---: | :---: | :---: | :---: |
| `reservas.create` | Criar novas reservas de recursos ou espaços | ✅ | ✅ | ✅ | ❌ |
| `reservas.view` | Visualizar reservas disponíveis ou criadas | ✅ | ✅ | ✅ | ✅ |
| `reservas.cancel` | Cancelar reservas em nome da instituição | ✅ | ✅ | ❌ | ❌ |
| `materiais.create` | Cadastrar novos materiais pedagógicos | ✅ | ❌ | ✅ | ❌ |
| `materiais.edit` | Editar materiais existentes | ✅ | ❌ | ✅ | ❌ |
| `materiais.delete` | Excluir materiais pedagógicos | ✅ | ❌ | ❌ | ❌ |
| `materiais.view` | Visualizar materiais pedagógicos | ✅ | ❌ | ✅ | ✅ |
| `planejamento.manage` | Gerenciar planejamentos de aula | ✅ | ❌ | ✅ | ❌ |
| `alunos.view` | Visualizar dados dos alunos | ✅ | ✅ | ✅ | ❌ |
| `alunos.grade` | Lançar notas e feedbacks | ❌ | ❌ | ✅ | ❌ |

## Como utilizar

* As políticas de autorização estão centralizadas em `App\Auth\Policies\PermissionMatrix` e expostas pelo `App\Models\User::can()`.
* Os middlewares `AdminMiddleware`, `SchoolMiddleware`, `ProfessorMiddleware` e `AlunoMiddleware` garantem que apenas o perfil correto acesse as áreas críticas.
* Para fluxos compartilhados entre administradores e professores, utilize `AdminOrProfessorMiddleware`.

## Migração de usuários

Ao executar `php database/migrate.php` a base será preparada com as tabelas `schools` e `users` e os dados do legado serão migrados da tabela `login`, preservando os vínculos de escola e perfil.

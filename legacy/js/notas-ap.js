/* js/notas.js — Notas via AJAX */
/* ═════════ VARIÁVEIS GLOBAIS ═════════ */
const dom = id => document.getElementById(id);
const selTurma   = dom('selectTurma');
const selMateria = dom('selectMateria');
const tbodyNotas = dom('tbodyNotas');

/* ═════════ FORMATAÇÃO DE DATA PROVA ═════════ */
function formatarDataProva(dataStr) {
  if (!dataStr || typeof dataStr !== 'string') return '';

  // timestamp numérico como string
  if (/^\d+$/.test(dataStr)) {
    const ts = parseInt(dataStr, 10);
    if (ts > 1000000000 && ts < 2000000000) {
      return new Date(ts * 1000).toLocaleDateString('pt-BR');
    }
  }

  // formato "YYYY-MM-DD"
  if (/^\d{4}-\d{2}-\d{2}$/.test(dataStr)) {
    const partes = dataStr.split('-');
    return new Date(
      parseInt(partes[0]),
      parseInt(partes[1]) - 1,
      parseInt(partes[2])
    ).toLocaleDateString('pt-BR');
  }

  // formato "YYYY-MM-DD HH:MM:SS"
  if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(dataStr)) {
    const dataISO = new Date(dataStr.replace(' ', 'T'));
    return isNaN(dataISO) ? '' : dataISO.toLocaleDateString('pt-BR');
  }

  return '';
}




/* ═════════ CARREGAR TURMAS (opcional) ═════════ */
async function carregarTurmas() {
    try {
        const resp = await fetch('includes/action-notas-ap.php?acao=turmas');
        const json = await resp.json();

        if (!json.sucesso) throw new Error(json.mensagem || 'Erro ao buscar turmas');

        selTurma.innerHTML = '<option value="">Todas</option>' +
            json.turmas.map(t => `<option value="${t}">${t}</option>`).join('');
    } catch (erro) {
        console.error(erro);
        selTurma.innerHTML = '<option value="">Erro ao carregar turmas</option>';
    }
}

/* ═════════ CARREGAR MATÉRIAS (opcional) ═════════ */
async function carregarMaterias() {
    const turma = selTurma.value;

    if (!turma) {
        selMateria.innerHTML = '<option value="">Todas</option>';
        return;
    }

    try {
        const resp = await fetch(`includes/action-notas-ap.php?acao=materias&turma=${encodeURIComponent(turma)}`);
        const json = await resp.json();

        if (!json.sucesso) throw new Error(json.mensagem || 'Erro ao buscar matérias');

        selMateria.innerHTML = '<option value="">Todas</option>' +
            json.materias.map(m => `<option value="${m}">${m}</option>`).join('');
    } catch (erro) {
        console.error(erro);
        selMateria.innerHTML = '<option value="">Erro ao carregar matérias</option>';
    }
}

/* ═════════ CARREGAR NOTAS (sem exigir filtros) ═════════ */
async function carregarNotas() {
    const turma   = selTurma?.value || 'todas';
    const materia = selMateria?.value || 'todas';

    try {
        const url = `includes/action-notas-ap.php?acao=listar&turma=${encodeURIComponent(turma)}&materia=${encodeURIComponent(materia)}`;
        const resp = await fetch(url);
        const json = await resp.json();

        if (!json.sucesso) {
            tbodyNotas.innerHTML =
                `<tr><td colspan="6" style="text-align:center;color:#c00">${json.mensagem}</td></tr>`;
            return;
        }

        const alunos = json.alunos;
        if (!alunos.length) {
            tbodyNotas.innerHTML =
                '<tr><td colspan="6" style="text-align:center;color:#777">Nenhum aluno encontrado.</td></tr>';
            return;
        }

        tbodyNotas.innerHTML = alunos.map(a => `
            <tr>
                <td>${a.turma}</td>
                <td>${a.nome}</td>
                <td>${a.materia}</td>
                <td>${a.nota ?? '-'}</td>
                <td>${formatarDataProva(a.data_prova)}</td>
                <td>
                    <button
                        class="btn-action btn-view"
                        id="btnView-${a.id_provas_online}_${a.id_aluno}"
                        data-id-prova-online="${a.id_provas_online}"
                        data-id-aluno="${a.id_aluno}"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');

    } catch (e) {
        console.error(e);
        tbodyNotas.innerHTML =
            '<tr><td colspan="6" style="text-align:center;color:#c00">Erro ao carregar notas.</td></tr>';
    }
}

/* ═════════ EVENTOS E DELEGAÇÃO ═════════ */
document.addEventListener('DOMContentLoaded', () => {
    carregarTurmas();
    carregarNotas(); // já carrega tudo ao abrir

    if (selTurma) selTurma.addEventListener('change', () => {
        carregarMaterias();
        carregarNotas();
    });

    if (selMateria) selMateria.addEventListener('change', carregarNotas);

    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-view');
        if (!btn) return;

        const idProvaOnline = btn.getAttribute('data-id-prova-online');
        const idAluno       = btn.getAttribute('data-id-aluno');

        if (!idProvaOnline || !idAluno) {
            alert('Dados insuficientes para abrir a prova.');
            return;
        }

        const url = `provas-visualizar.php?id=${idProvaOnline}&de=notas&id_aluno=${idAluno}`;
        window.open(url, '_blank');
    });
});

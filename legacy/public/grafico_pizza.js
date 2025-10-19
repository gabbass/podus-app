// Gráfico de Pizza - Alunos por Turma
        const ctx = document.getElementById('alunosTurmaChart').getContext('2d');
        const alunosTurmaChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($turmas);?>,
                datasets: [{
                    data: <?php echo json_encode($quantidades); ?>,
                    backgroundColor: <?php echo json_encode($cores); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
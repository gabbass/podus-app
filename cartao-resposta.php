<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartão Resposta - Prova de Geografia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .page {
            border: 1px solid #000;
            padding: 20px;
            margin-bottom: 30px;
            page-break-after: always;
        }
        
        .instructions {
            margin-bottom: 20px;
        }
        
        .instructions h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 20px;
        }
        
        .instructions h2 {
            font-size: 18px;
            margin: 15px 0 10px;
        }
        
        .instructions ul {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .card {
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .student-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .student-info div {
            border-bottom: 1px solid #000;
            padding: 5px 0;
        }
        
        .student-info label {
            font-weight: bold;
            display: block;
            font-size: 12px;
        }
        
        .answer-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .column {
            width: 48%;
        }
        
        .question {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .question-number {
            width: 30px;
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
        }
        
        .options {
            display: flex;
            gap: 10px;
        }
        
        .option {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .circle {
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .circle:hover {
            background-color: #e9e9e9;
        }
        
        .option-label {
            font-size: 12px;
            margin-top: 2px;
        }
        
        .example {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        
        .example-circle {
            width: 20px;
            height: 20px;
            border: 2px solid #000;
            border-radius: 50%;
            background-color: #000;
        }
        
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        
        .subtitle {
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .signature {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 20px;
            width: 100%;
            text-align: center;
            font-size: 12px;
        }
        
        .qr-code {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .card-container {
            position: relative;
        }
        
        @media print {
            .page {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Instructions Page -->
        <div class="page instructions">
            <h1>Instruções para a Avaliação Impressa</h1>
            <h2>Recomendações para impressão, preenchimento e digitalização das folhas de gabarito</h2>
            
            <h3>Impressão</h3>
            <ul>
                <li>Imprimir em folha de tamanho A4, papel branco, na orientação vertical;</li>
                <li>Utilizar impressão de boa qualidade, com no mínimo 300 dpi de resolução;</li>
                <li>Após gerar o arquivo em Word com os enunciados para impressão, não alterar a ordem das questões, pois isso resultará na correção incorreta da prova;</li>
            </ul>
            
            <h3>Preenchimento</h3>
            <ul>
                <li>Orientar os estudantes a preencher corretamente os círculos de marcação de resposta, conforme o exemplo de preenchimento fornecido, utilizando caneta azul ou preta;</li>
                <li>Instruir os alunos a não rasurar partes da folha de gabarito e a não escrever em espaços não destinados para isso;</li>
            </ul>
            
            <h3>Digitalização</h3>
            <ul>
                <li>Digitalizar as folhas de gabarito utilizando scanner, na configuração tons de cinza;</li>
                <li>A qualidade do escaneamento deve ser de pelo menos 300 dpi;</li>
                <li>Digitalizar as folhas de gabarito na orientação correta: vertical e de cabeça para cima;</li>
                <li>Não importar folhas de gabarito de alunos ausentes, pois o sistema de correção irá considerar como aluno presente, com todas as respostas em branco.</li>
            </ul>
        </div>
        
        <!-- Answer Card Page -->
        <div class="page card-container">
            <div class="qr-code">
                <!-- Simulated QR code with CSS -->
                <svg width="70" height="70" viewBox="0 0 70 70">
                    <rect x="5" y="5" width="60" height="60" fill="white" stroke="black" stroke-width="1"/>
                    <g fill="black">
                        <!-- QR code pattern simulation -->
                        <rect x="10" y="10" width="5" height="5"/>
                        <rect x="15" y="10" width="5" height="5"/>
                        <rect x="20" y="10" width="5" height="5"/>
                        <rect x="10" y="15" width="5" height="5"/>
                        <rect x="20" y="15" width="5" height="5"/>
                        <rect x="10" y="20" width="5" height="5"/>
                        <rect x="15" y="20" width="5" height="5"/>
                        <rect x="20" y="20" width="5" height="5"/>
                        
                        <rect x="50" y="10" width="5" height="5"/>
                        <rect x="55" y="10" width="5" height="5"/>
                        <rect x="50" y="15" width="5" height="5"/>
                        <rect x="55" y="15" width="5" height="5"/>
                        <rect x="50" y="20" width="5" height="5"/>
                        <rect x="55" y="20" width="5" height="5"/>
                        
                        <rect x="10" y="50" width="5" height="5"/>
                        <rect x="15" y="50" width="5" height="5"/>
                        <rect x="20" y="50" width="5" height="5"/>
                        <rect x="10" y="55" width="5" height="5"/>
                        <rect x="20" y="55" width="5" height="5"/>
                        
                        <rect x="35" y="35" width="5" height="5"/>
                        <rect x="40" y="40" width="5" height="5"/>
                        <rect x="30" y="30" width="5" height="5"/>
                        <rect x="45" y="25" width="5" height="5"/>
                        <rect x="25" y="45" width="5" height="5"/>
                    </g>
                </svg>
            </div>
            
            <div class="card">
                <div class="header">
                    <h1 class="title">Cartão Resposta</h1>
                    <p class="subtitle">Prova de Geografia - Território Brasileiro</p>
                </div>
                
                <div class="example">
                    <div class="example-circle"></div>
                    <p>Para todas as marcações neste CARTÃO RESPOSTA, preencha os círculos completamente e com nitidez utilizando caneta preta ou azul, conforme exemplo ao lado. NÃO RASURE O CARTÃO RESPOSTA, sob pena de ANULAÇÃO DA AVALIAÇÃO.</p>
                </div>
                
                <div class="student-info">
                    <div>
                        <label>Escola:</label>
                    </div>
                    <div>
                        <label>Matrícula:</label>
                    </div>
                    <div>
                        <label>Nome:</label>
                    </div>
                    <div>
                        <label>Série:</label>
                        <label>Turma:</label>
                    </div>
                </div>
                
                <div class="answer-grid">
                    <div class="column">
                        <h3>Respostas 1 - 15</h3>
                        
                        <!-- Questions 1-15 -->
                        <div class="question">
                            <div class="question-number">01</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Repeat for questions 2-15 -->
                        <!-- Question 2 -->
                        <div class="question">
                            <div class="question-number">02</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 3 -->
                        <div class="question">
                            <div class="question-number">03</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 4 -->
                        <div class="question">
                            <div class="question-number">04</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 5 -->
                        <div class="question">
                            <div class="question-number">05</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 6 -->
                        <div class="question">
                            <div class="question-number">06</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 7 -->
                        <div class="question">
                            <div class="question-number">07</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 8 -->
                        <div class="question">
                            <div class="question-number">08</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 9 -->
                        <div class="question">
                            <div class="question-number">09</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 10 -->
                        <div class="question">
                            <div class="question-number">10</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 11 -->
                        <div class="question">
                            <div class="question-number">11</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 12 -->
                        <div class="question">
                            <div class="question-number">12</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 13 -->
                        <div class="question">
                            <div class="question-number">13</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 14 -->
                        <div class="question">
                            <div class="question-number">14</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 15 -->
                        <div class="question">
                            <div class="question-number">15</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column">
                        <h3>Respostas 16 - 25</h3>
                        
                        <!-- Questions 16-25 -->
                        <div class="question">
                            <div class="question-number">16</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 17 -->
                        <div class="question">
                            <div class="question-number">17</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 18 -->
                        <div class="question">
                            <div class="question-number">18</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 19 -->
                        <div class="question">
                            <div class="question-number">19</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">D</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">E</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 20 -->
                        <div class="question">
                            <div class="question-number">20</div>
                            <div class="options">
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">A</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">B</div>
                                </div>
                                <div class="option">
                                    <div class="circle"></div>
                                    <div class="option-label">C</div>
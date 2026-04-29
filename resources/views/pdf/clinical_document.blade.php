@php
    $svg = base64_encode(file_get_contents(public_path('background.svg')));
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <title>Documento Clínico — Vitalfy</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: Arial, sans-serif;
                font-size: 13px;
                color: #1e293b;
                line-height: 1.6;
            }
            .background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                object-fit: cover;
            }
            .page {
                padding: 2.5rem 2.75rem;
            }
            .doc-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding-bottom: 1.25rem;
                border-bottom: 2px solid #185FA5;
                margin-bottom: 1.75rem;
            }
            .doc-header-left h1 {
                font-size: 18px;
                font-weight: 700;
                color: #185FA5;
                letter-spacing: -0.01em;
            }
            .doc-header-left p {
                font-size: 11px;
                color: #64748b;
                margin-top: 3px;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }
            .doc-header-right {
                text-align: right;
            }
            .doc-header-right .brand {
                font-size: 15px;
                font-weight: 700;
                color: #185FA5;
            }
            .doc-header-right .brand span {
                color: #0C447C;
            }
            .doc-header-right .date {
                font-size: 11px;
                color: #64748b;
                margin-top: 3px;
            }
            .patient-bar {
                background-color: #EBF3FC;
                border-left: 3px solid #185FA5;
                border-radius: 0 6px 6px 0;
                padding: 0.75rem 1rem;
                margin-bottom: 1.75rem;
                display: flex;
                gap: 2rem;
                flex-wrap: wrap;
            }
            .patient-bar .field label {
                font-size: 10px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.07em;
                display: block;
                margin-bottom: 2px;
            }
            .patient-bar .field span {
                font-size: 13px;
                font-weight: 600;
                color: #0C447C;
            }
            .content h1 {
                font-size: 15px;
                font-weight: 700;
                color: #185FA5;
                margin-top: 1.5rem;
                margin-bottom: 0.5rem;
                padding-bottom: 0.3rem;
                border-bottom: 1px solid #BFDBF7;
            }
            .content h2 {
                font-size: 13px;
                font-weight: 700;
                color: #1e293b;
                margin-top: 1rem;
                margin-bottom: 0.35rem;
            }
            .content h3 {
                font-size: 12px;
                font-weight: 700;
                color: #334155;
                margin-top: 0.85rem;
                margin-bottom: 0.3rem;
            }
            .content p {
                font-size: 13px;
                color: #334155;
                margin-bottom: 0.5rem;
                line-height: 1.65;
            }
            .content ul,
            .content ol {
                margin-left: 1.5rem;
                margin-bottom: 0.75rem;
            }
            .content li {
                font-size: 13px;
                color: #334155;
                margin-bottom: 0.25rem;
                line-height: 1.6;
            }
            .content strong {
                color: #0f172a;
                font-weight: 700;
            }
            .content em {
                color: #475569;
                font-style: italic;
            }
            .content blockquote {
                background: #EBF3FC;
                border-left: 3px solid #185FA5;
                border-radius: 0 6px 6px 0;
                padding: 0.65rem 1rem;
                margin: 0.75rem 0;
                font-size: 13px;
                color: #0C447C;
            }
            .content hr {
                border: none;
                border-top: 1px solid #e2e8f0;
                margin: 1.25rem 0;
            }
            .doc-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 0.65rem 2.75rem;
                border-top: 1px solid #BFDBF7;
                display: flex;
                justify-content: flex-end;
                align-items: center;
            }
            .doc-footer .footer-brand {
                font-size: 10px;
                font-weight: 700;
                color: #185FA5;
                letter-spacing: 0.04em;
            }
            .doc-footer .footer-note {
                font-size: 10px;
                color: #94a3b8;
            }
            .doc-footer .footer-page::before {
                content: "Página " counter(page);
            }
            .doc-footer .footer-page {
                font-size: 10px;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <img class="background" src="data:image/svg+xml;base64,{{ $svg }}">
        <div class="page">
            <div class="doc-header">
                <div class="doc-header-left">
                    <h1>{{ $template_name }}</h1>
                    <p>Gerado por: {{ $patient_name }}</p>
                </div>
                <div class="doc-header-right">
                    <div class="brand">Vita<span>lfy</span></div>
                    <div class="date">{{ $created_at }}</div>
                </div>
            </div>

            <!-- Identificação do paciente (alimentar com variáveis reais) -->
            <!-- @if(!empty($patient) || !empty($template) || !empty($type))
            <div class="patient-bar">
                @if(!empty($patient))
                <div class="field">
                    <label>Paciente</label>
                    <span>{{ $patient }}</span>
                </div>
                @endif
                @if(!empty($template))
                <div class="field">
                    <label>Template</label>
                    <span>{{ $template }}</span>
                </div>
                @endif
                @if(!empty($type))
                <div class="field">
                    <label>Tipo de atendimento</label>
                    <span>{{ $type }}</span>
                </div>
                @endif
            </div>
            @endif -->

            <div class="content">
                {!! $content !!}
            </div>
        </div>

        <div class="doc-footer">
            <!-- <span class="footer-brand">VITALFY</span>
            <span class="footer-note">Documento gerado por inteligência artificial. Sujeito à revisão clínica.</span> -->
            <span class="footer-page"></span>
        </div>
    </body>
</html>
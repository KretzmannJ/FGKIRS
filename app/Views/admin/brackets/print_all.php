<?php
/**
 * Standalone print page: every drawn bracket of the event, one landscape page per category.
 *
 * @var array $championship
 * @var array $sheets list of bracket states (BracketService::state)
 */
$e = static fn (?string $s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title><?= $e($championship['title']) ?> – Chaves</title>
<style>
    @page { size: A4 landscape; margin: 10mm; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: #0f172a; background: #fff; }
    .toolbar { padding: 12px 16px; background: #0f172a; color: #fff; display: flex; gap: 12px; align-items: center; font-size: 14px; }
    .toolbar button { background: #b91c1c; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-weight: 600; cursor: pointer; }
    .sheet { break-before: page; page-break-before: always; break-inside: avoid; page-break-inside: avoid; padding: 8px 12px; }
    .sheet:first-of-type { break-before: auto; page-break-before: auto; }
    .sheet h2 { margin: 0; font-size: 18px; }
    .sheet p.sub { margin: 2px 0 8px; font-size: 11px; color: #64748b; }
    .fit { transform-origin: top left; width: max-content; }
    .rounds { display: flex; gap: 1.5rem; }
    .round { display: flex; flex-direction: column; width: 12rem; }
    .round-title { text-align: center; font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748b; padding-bottom: .4rem; }
    .round-body { display: flex; flex-direction: column; flex: 1; }
    .match-wrap { position: relative; flex: 1; display: flex; align-items: center; padding: .3rem 0; }
    .match { position: relative; width: 100%; border: 1px solid #94a3b8; border-radius: .4rem; background: #fff; }
    .slot { display: flex; justify-content: space-between; gap: .4rem; padding: .3rem .5rem; font-size: .72rem; border-bottom: 1px solid #e2e8f0; min-height: 2.3rem; align-items: center; }
    .slot:last-child { border-bottom: 0; }
    .slot b { display: block; font-weight: 600; }
    .slot small { display: block; color: #64748b; font-size: .6rem; }
    .slot.empty { color: #94a3b8; font-style: italic; }
    .slot.win b { color: #047857; }
    .score { font-weight: 700; font-size: .7rem; }
    .round:not(:last-child) .match-wrap::after { content: ""; position: absolute; right: -.75rem; top: 50%; width: .75rem; border-top: 2px solid #94a3b8; }
    .round:not(:last-child) .match-wrap:nth-child(odd)::before { content: ""; position: absolute; right: -.75rem; top: 50%; height: 50%; border-right: 2px solid #94a3b8; }
    .round:not(:last-child) .match-wrap:nth-child(even)::before { content: ""; position: absolute; right: -.75rem; bottom: 50%; height: 50%; border-right: 2px solid #94a3b8; }
    .round:not(:first-child) .match::before { content: ""; position: absolute; left: -.75rem; top: 50%; width: .75rem; border-top: 2px solid #94a3b8; }
    @media print { .toolbar { display: none; } }
</style>
</head>
<body>
<div class="toolbar">
    <span><?= count($sheets) ?> chave(s) · papel A4 paisagem</span>
    <button type="button" onclick="window.print()">Imprimir / salvar em PDF</button>
</div>

<?php if (!$sheets): ?>
    <p style="padding:24px">Nenhuma chave sorteada neste evento.</p>
<?php endif; ?>

<?php foreach ($sheets as $s): ?>
    <section class="sheet">
        <h2><?= $e($championship['title']) ?> — <?= $e($s['category']['shortName']) ?></h2>
        <p class="sub"><?= (int) $s['eligible'] ?> participante(s) · chave de <?= (int) $s['bracket']['size'] ?> vagas</p>
        <div class="fit"><div class="rounds">
            <?php foreach ($s['rounds'] as $r): ?>
                <div class="round">
                    <div class="round-title"><?= $e($r['label']) ?></div>
                    <div class="round-body">
                        <?php foreach ($r['matches'] as $m): ?>
                            <div class="match-wrap"><div class="match">
                                <?php foreach (['a' => 'scoreA', 'b' => 'scoreB'] as $side => $scoreKey): $p = $m[$side]; ?>
                                    <?php if (!$p): ?>
                                        <div class="slot empty"><?= $r['round'] === 1 ? 'Vaga livre' : '&nbsp;' ?></div>
                                    <?php else: ?>
                                        <div class="slot <?= $m['winner'] === $p['id'] ? 'win' : '' ?>">
                                            <div><b><?= $e($p['name']) ?></b><small><?= $e($p['dojo']) ?></small></div>
                                            <?php if ($m[$scoreKey] !== null && $m[$scoreKey] !== ''): ?><span class="score"><?= $e($m[$scoreKey]) ?></span><?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div></div>
    </section>
<?php endforeach; ?>

<script>
    // Shrink each bracket to fit one landscape A4 page (about 1100 x 740 px usable).
    function fit() {
        document.querySelectorAll('.sheet').forEach(sheet => {
            const box = sheet.querySelector('.fit');
            box.style.transform = 'none';
            const maxW = 1010, maxH = 640; // A4 landscape minus 10mm margins
            const scale = Math.min(1, maxW / box.scrollWidth, maxH / box.scrollHeight);
            box.style.transform = 'scale(' + scale + ')';
            sheet.querySelector('.fit').parentElement.style.height = (box.scrollHeight * scale) + 'px';
        });
    }
    window.addEventListener('load', fit);
    window.addEventListener('beforeprint', fit);
</script>
</body>
</html>

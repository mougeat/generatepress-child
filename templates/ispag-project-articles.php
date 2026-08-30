<?php
/**
 * Template pour l'affichage du contenu desarticles d'un projet
 * Variables attendues : $datas (array)
 */

$deal_id            = $datas['deal_id']    ?? '';
$can_view_prices    = $datas['can_view_prices']    ?? false;


$article_renderer = new ISPAG_Project_views_Renderer();

$article_renderer->render_project_stat($deal_id, $can_view_prices);
?>

<div class="ispag-card">
<?php
$article_renderer->display_ispag_project_articles($deal_id, 999);
?>
</div>

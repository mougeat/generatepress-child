<?php
/**
 * Template pour l'affichage du contenu desarticles d'un projet
 * Variables attendues : $datas (array)
 */

$transactions_list_full     = $datas['transactions_list_full']    ?? '';
$link_new_project           = $datas['link_new_project']    ?? '';
$user_id                    = $datas['user_id']    ?? '';


?>

<div class="ispag-card ispag-transactions-card">
    <h5>
        <?php _e( 'Transactions', 'ispag-crm' ); ?> (<?php echo count($transactions_list_full); ?>)
        <span style="font-size: 12px; color: #007bff; cursor: pointer;"><a href="<?php echo $link_new_project; ?>" target="_blank">+ <?php _e( 'Add', 'ispag-crm' ); ?></a></span>
    </h5>
    <?php 
    // Définition de la constante si elle n'est pas déjà définie dans un fichier de configuration
    if ( ! defined( 'NB_TRANSACTIONS_RIGHT' ) ) {
        define( 'NB_TRANSACTIONS_RIGHT', 5 );
    }
    $nb_transactions = 0;
    foreach ( $transactions_list_full as $transaction ): 
        
        $nb_transactions++;

        // 2. Vérifier si on a atteint la limite après l'incrémentation
        // Si $nb_transactions est strictement supérieur à la limite, on arrête la boucle.
        if ( $nb_transactions > NB_TRANSACTIONS_RIGHT ) {
            break; // Arrête l'exécution de la boucle foreach
        }
        $current_stage_label      = $transaction->stage_label ?? __('Non défini', 'ispag-crm');
        $current_stage_color      = $transaction->stage_color ?? '#cccccc';

        // CORRECTION ICI : Le repo injecte stage_label et stage_color
        $current_stage_label = !empty($transaction->stage_label) ? $transaction->stage_label : __('Non défini', 'ispag-crm');
        $current_stage_color = !empty($transaction->stage_color) ? $transaction->stage_color : '#cccccc';
        ?>
        <div class="ispag-card" style="font-size: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="ispag-transaction-item">
                    <strong>
                        <a href="<?php echo esc_url($transaction->get_deal_detail_link()); ?>"><?php echo $transaction->project_name; ?></a>
                    </strong>
                    <p><?php _e( 'Amount', 'ispag-crm' ); ?>: <?php echo number_format( (float)$transaction->total_excl_vat, 2, '.', '\'' ) . ' CHF'; ?></p>
                    <p><?php _e( 'Closing date', 'ispag-crm' ); ?>: <?php echo date_i18n( get_option('date_format'), strtotime( $transaction->closing_date ) ); ?></p>
                    <p><?php _e( 'Transaction phase', 'ispag-crm' ); ?>: 
                        <span class="ispag-status-badge" style="background-color: <?php echo esc_attr($current_stage_color); ?>; ">
                            <?php echo esc_html($current_stage_label); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    <?php 
    
    endforeach; 
    
    if ( $nb_transactions > NB_TRANSACTIONS_RIGHT ) {
        $company_deal_url = home_url( '/deals-list/?search=user-' . $user_id );
        ?>
        <a href="<?php echo $company_deal_url; ?>" class="ispag-button-link"><?php _e( 'Show all transactions', 'ispag-crm' ); ?></a>
    <?php
    }
    ?>
</div>
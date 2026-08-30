<?php
/**
 * Template pour l'affichage du contenu desarticles d'un projet
 * Variables attendues : $datas (array)
 */

$associated_companies_list_full     = $datas['associated_companies_list_full']    ?? '';
$user_id                            = $datas['user_id']    ?? '';
$deal_id                            = $datas['deal_id']    ?? '';
$company_name                       = $datas['company_name']    ?? '';
$initials                           = $datas['initials']    ?? '';

?>

<div class="ispag-card ispag-company-card">
    <h5>
        <?php _e( 'Company', 'ispag-crm' ); ?> (<?php echo count($associated_companies_list_full); ?>) 
        <span id="open-add-company-modal"  
            style="font-size: 12px; color: #007bff; cursor: pointer;" 
            data-contact-id="<?php echo absint($user_id); ?>">
            + <?php _e( 'Add', 'ispag-crm' ); ?>
        </span>
    </h5>
    <?php 
    if (class_exists( 'ISPAG_Crm_Company_Repository' ) ){
        $company_repo = new ISPAG_Crm_Company_Repository();

        
        
        foreach ($associated_companies_list_full as $company_id) {
            
            $company = $company_repo->get_company_by_viag_id($company_id);
            $company_app_url = home_url( '/company/' . $company->viag_id . '/' );

            // 1. On récupère le domaine (assure-tu que la propriété est bien 'compagny_domain' ou 'domain')
            $company_domain = !empty($company->compagny_domain) ? $company->compagny_domain : '';
            
            // 2. Génération du favicon via le domaine
            
            $favicon = $company->favicon ?? null;

            ?>
            <div class="ispag-card" style="font-size: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="ispag-mini-profile-pic" >
                        <?php
                        if ($favicon) {
                        ?>
                            <img src="<?php echo esc_url( $favicon ); ?>" 
                                alt="<?php echo esc_attr( $company_name ); ?>"
                                class="ispag-avatar-img"
                                style="width:20px; height:20px;"> 
                            <?php
                            
                        } else {
                            // Afficher les deux premières lettres du nom de l'entreprise
                            // $initials = strtoupper( substr( $company_name, 0, 1 ) . substr( $company_name, strpos($company_name, ' ') + 1, 1 ) );
                            echo esc_html( $initials ); 
                        }
                        ?>
                        
                    </div>
                    <strong>
                        <a href="<?php echo esc_url($company_app_url); ?>"><?php echo $company->company_name; ?></a>
                    </strong>
                    <span 
                        class="ispag-remove-association" 
                        data-action="remove-contact-from-company"
                        data-contact-id="<?php echo absint($user_id); ?>"
                        data-company-id="<?php echo absint($company->viag_id); ?>"
                        data-deal-id="<?php echo absint($deal_id); ?>"
                        title="<?php esc_attr_e( 'Remove association', 'ispag-crm' ); ?>"
                        style="color: #e74c3c; cursor: pointer;"
                    >
                        <span class="dashicons dashicons-trash"></span>
                    </span>
                </div>
                <p style="margin: 5px 0 0;"><?php _e( 'City', 'ispag-crm' ); ?>: <?php echo $company->city; ?></p>
                <p style="margin: 5px 0 0;"><?php _e( 'Phone number', 'ispag-crm' ); ?>: <?php echo $company->phone; ?></p>
                
            </div>
            <?php
        }
    }
    
    ?>
    <input type="hidden" id="hidden_company_name"  value="<?php echo $company->company_name; ?>"/>
</div>

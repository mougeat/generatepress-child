<?php
/**
 * Aide au bandeau « chiffres clés » partagé par les fiches deal, company et contact.
 * Les calculs sont faits ici pour que les trois pages restent alignées (mêmes seuils, mêmes libellés).
 */
if ( ! class_exists( 'ISPAG_Entity_Summary' ) ) :

class ISPAG_Entity_Summary {

    const CONTACT_WARN_DAYS   = 14;
    const CONTACT_DANGER_DAYS = 30;

    /** Nombre de jours entiers entre une date passée et aujourd'hui (null si pas de date). */
    public static function days_since( $datetime ) {
        $ts = $datetime ? strtotime( $datetime ) : 0;
        if ( ! $ts ) return null;
        return max( 0, (int) round( ( strtotime( 'today' ) - strtotime( date( 'Y-m-d', $ts ) ) ) / DAY_IN_SECONDS ) );
    }

    /** Prochaine tâche ouverte (échéance la plus proche) parmi des activités déjà chargées. */
    public static function next_task( $activities ) {
        $today = strtotime( 'today' );
        $next  = null;
        foreach ( (array) $activities as $act ) {
            if ( empty( $act->is_task ) || ! empty( $act->is_completed ) ) continue;
            $due = ! empty( $act->due_date ) ? strtotime( $act->due_date ) : 0;
            if ( $next === null || ( $due && $due < $next['ts'] ) ) {
                $next = [
                    'ts'        => $due ?: PHP_INT_MAX,
                    'title'     => $act->title ?? '',
                    'overdue'   => $due && $due < $today,
                    'due_label' => $due ? date_i18n( 'd.m.Y', $due ) : __( 'No due date', 'ispag-crm' ),
                ];
            }
        }
        return $next;
    }

    /** Répartition d'une liste de deals : ouverts / gagnés / perdus, avec montants. */
    public static function deals_totals( $deals ) {
        $t = [ 'open' => [ 'count' => 0, 'amount' => 0.0 ], 'won' => [ 'count' => 0, 'amount' => 0.0 ], 'lost' => [ 'count' => 0, 'amount' => 0.0 ] ];
        foreach ( (array) $deals as $d ) {
            $status = (int) ( $d->project_db_status ?? 0 );
            // Même règle que le Kanban : statut 1 + database_status 11 = encore ouvert
            if ( $status === 0 || ( $status === 1 && (int) ( $d->database_status ?? 0 ) === 11 ) ) $k = 'open';
            elseif ( $status === 1 ) $k = 'won';
            else $k = 'lost';
            $t[ $k ]['count']++;
            $t[ $k ]['amount'] += (float) ( $d->total_excl_vat ?? 0 );
        }
        return $t;
    }

    /** Tuile « Dernier contact » (orange > 14 j, rouge > 30 j ou jamais). */
    public static function last_contact_tile( $datetime ) {
        $days = self::days_since( $datetime );
        return [
            'icon'  => 'backup',
            'label' => __( 'Last contacted', 'ispag-crm' ),
            'value' => $datetime ? date_i18n( 'd.m.Y', strtotime( $datetime ) ) : __( 'Never', 'ispag-crm' ),
            'sub'   => $days === null ? __( 'No activity logged', 'ispag-crm' ) : sprintf( __( '%d days ago', 'ispag-crm' ), $days ),
            'level' => ( $days === null || $days > self::CONTACT_DANGER_DAYS ) ? 'danger' : ( $days > self::CONTACT_WARN_DAYS ? 'warn' : '' ),
        ];
    }

    /** Tuile « Prochaine tâche ». */
    public static function next_task_tile( $task ) {
        return [
            'icon'  => 'yes-alt',
            'label' => __( 'Next task', 'ispag-crm' ),
            'value' => $task ? ( $task['title'] ?: __( 'Task', 'ispag-crm' ) ) : __( 'None planned', 'ispag-crm' ),
            'sub'   => $task ? $task['due_label'] : __( 'Plan the next step', 'ispag-crm' ),
            'level' => $task ? ( $task['overdue'] ? 'danger' : '' ) : 'warn',
        ];
    }

    /** Tuile « Deals ouverts ». */
    public static function open_deals_tile( $totals ) {
        $o = $totals['open'];
        return [
            'icon'  => 'portfolio',
            'label' => __( 'Open deals', 'ispag-crm' ),
            'value' => (string) $o['count'],
            'sub'   => number_format( $o['amount'], 0, '.', '\'' ) . ' CHF',
            'level' => '',
        ];
    }

    /** Alertes communes : contact trop ancien, pas de tâche. */
    public static function common_alerts( $datetime, $task ) {
        $alerts = [];
        $days   = self::days_since( $datetime );
        if ( $days === null || $days > self::CONTACT_DANGER_DAYS ) {
            $alerts[] = [ 'level' => 'danger', 'text' => sprintf( __( 'No contact for over %d days', 'ispag-crm' ), self::CONTACT_DANGER_DAYS ) ];
        }
        if ( ! $task ) {
            $alerts[] = [ 'level' => 'warn', 'text' => __( 'No follow-up task planned', 'ispag-crm' ) ];
        }
        return $alerts;
    }

    /** Rend le bandeau. */
    public static function render( array $tiles, array $alerts = [] ) {
        return ispag_get_template( 'entity-summary', [ 'tiles' => $tiles, 'alerts' => $alerts ] );
    }
}

endif;

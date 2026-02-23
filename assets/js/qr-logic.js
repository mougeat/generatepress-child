jQuery(document).ready(function($) {
    console.log("ISPAG QR System initialized for serial: " + $('#serial-display').text());

    // Exemple : Animation de l'apparition des specs
    $('.card').hide().fadeIn(800);

    // Futur : Appel Ajax pour récupérer les données du DXF ou de la DB
});
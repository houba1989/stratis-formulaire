<?php

/**
 * Plugin Name: Stratis Formulaire
 * Plugin URI: https://www.stratisworldwide.com/
 * Description: Plugin Stratis Formulaire.
 * Version: 1.0.0
 * Author: Iheb Grayâa
 * Author URI: https://www.facebook.com/iheb.grayaa.01/
 **/

// Définir une constante pour le chemin du fichier du plugin
if (!defined('STRATIS_FORM_PLUGIN_FILE')) {
    define('STRATIS_FORM_PLUGIN_FILE', __FILE__);
}

// Enfile le fichier CSS du plugin
function STRATIS_assets()
{
    wp_enqueue_style('style', plugin_dir_url(STRATIS_FORM_PLUGIN_FILE) . '/assets/css/style.css', false);
}
add_action('wp_enqueue_scripts', 'STRATIS_assets');

// Définit le shortcode [stratis-formulaire]
function formulaire_stratis()
{
    ob_start();

    // Vérifie si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stratis_submit'])) {
        $titre = sanitize_text_field($_POST['stratis_titre']);
        $texte = sanitize_text_field($_POST['stratis_texte']);

        // Vérifie si les champs titre et texte sont vides
        if (empty($titre) || empty($texte)) {
            echo '<div class="error">Les champs "Titre" et "Texte" sont obligatoires.</div>';
        } else {
            // Vérifie si un article avec le même titre existe déjà
            $existing_post = get_page_by_title($titre, OBJECT, 'post');
            if ($existing_post === null) {
                // Insère un nouvel article
                $post_id = wp_insert_post(array(
                    'post_title' => $titre,
                    'post_content' => $texte,
                    'post_status' => 'draft',
                    'post_type' => 'post',
                ));

                // Envoie une notification par e-mail
                $to = get_option('admin_email');
                $subject = 'Nouveau article créé';
                $message = "Un nouveau article a été créé avec le titre : $titre\n\n$texte";
                wp_mail($to, $subject, $message);

                echo '<div class="success">Article créé avec succès !</div>';
            } else {
                echo '<div class="error">Un article avec le même titre existe déjà.</div>';
            }
        }
    }

    // Affiche le formulaire
    ?>
    <form method="post">
        <div class="form-input">
            <label for="stratis_titre">Titre :</label>
            <input type="text" class="titre" id="stratis_titre" name="stratis_titre" required><br>
        </div>
        <div class="form-input">
            <label for="stratis_texte">Texte :</label>
            <textarea class="stratis_texte" id="stratis_texte" name="stratis_texte" required></textarea><br>
        </div>
        <input class="btn-startis" type="submit" name="stratis_submit" value="Envoyer">
    </form>
    <?php

    return ob_get_clean();
}
add_shortcode('stratis-formulaire', 'formulaire_stratis');

// Fonction pour créer automatiquement une page lorsque le plugin est activé
function creer_page_automatique()
{
    $page_title = 'Formulaire de création un post';
    $page_content = '[stratis-formulaire]';

    // Vérifie si la page existe déjà
    $page = get_page_by_title($page_title);

    // Si la page n'existe pas, la crée
    if (!$page) {
        $page = array(
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page',
        );

        wp_insert_post($page);
    }
}

// Enregistre le crochet d'activation pour créer la page
register_activation_hook(__FILE__, 'creer_page_automatique');

<?php
/**
 * Plugin Name: Stratis Formulaire
 * Plugin URI: https://www.stratisworldwide.com/
 * Description: Plugin Stratis Formulaire.
 * Version: 1.0.0
 * Author: Iheb Grayâa
 * Author URI: https://www.facebook.com/iheb.grayaa.01/
 **/

// Define a constant for the plugin file path
if (!defined('STRATIS_FORM_PLUGIN_FILE')) {
    define('STRATIS_FORM_PLUGIN_FILE', __FILE__);
}

// Enqueue the plugin's CSS file
function stratis_assets()
{
    wp_enqueue_style('stratis-style', plugin_dir_url(STRATIS_FORM_PLUGIN_FILE) . '/assets/css/style.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'stratis_assets');

// Define the shortcode [stratis-formulaire]
function formulaire_stratis()
{
    ob_start();

    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stratis_submit'])) {
        $titre = sanitize_text_field($_POST['stratis_titre']);
        $texte = sanitize_text_field($_POST['stratis_texte']);

        // Check if title and text fields are empty
        if (empty($titre) || empty($texte)) {
            echo '<div class="error">Les champs "Titre" et "Texte" sont obligatoires.</div>';
        } else {
            // Check if a post with the same title already exists
            $existing_post = get_page_by_title($titre, OBJECT, 'post');
            if ($existing_post === null) {
                // Insert a new post
                $post_id = wp_insert_post(array(
                    'post_title' => $titre,
                    'post_content' => $texte,
                    'post_status' => 'draft', // You may want to change this to 'publish'
                    'post_type' => 'post',
                ));

                // Send an email notification
                $to = get_option('admin_email');
                $subject = 'Nouvel article créé';
                $message = "Un nouvel article a été créé avec le titre : $titre\n\n$texte";
                wp_mail($to, $subject, $message);

                echo '<div class="success">Article créé avec succès !</div>';
            } else {
                echo '<div class="error">Un article avec le même titre existe déjà.</div>';
            }
        }
    }

    // Display the form
    ?>
    <form method="post">
        <div class="form-input">
            <label for="stratis_titre">Titre</label>
            <input type="text" class="stratis_titre" id="stratis_titre" name="stratis_titre" required><br>
        </div>
        <div class="form-input">
            <label for="stratis_texte">Texte</label>
            <textarea class="stratis_texte" id="stratis_texte" name="stratis_texte" required></textarea><br>
        </div>
        <input type="submit" class="btn-stratis" name="stratis_submit" value="Envoyer">
    </form>
    <?php

    return ob_get_clean();
}
add_shortcode('stratis-formulaire', 'formulaire_stratis');

// Function to automatically create a page when the plugin is activated
function create_auto_page()
{
    $page_title = 'Formulaire de création d\'un post';
    $page_content = '[stratis-formulaire]';

    // Check if the page already exists
    $page = get_page_by_title($page_title);

    // If the page doesn't exist, create it
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

// Register the activation hook to create the page
register_activation_hook(__FILE__, 'create_auto_page');

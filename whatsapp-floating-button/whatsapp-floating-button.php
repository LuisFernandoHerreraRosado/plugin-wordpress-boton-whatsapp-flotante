<?php
/**
 * Plugin Name: WhatsApp Floating Button
 * Plugin URI:  https://example.com/whatsapp-floating-button
 * Description: Agrega un botón flotante de WhatsApp a tu sitio web con opciones configurables desde el panel de administración.
 * Version:     1.0.0
 * Author:      Jules
 * Author URI:  https://example.com
 * License:     GPL2
 * Text Domain: whatsapp-floating-button
 */

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definir constantes del plugin
define( 'WFB_PATH', plugin_dir_path( __FILE__ ) );
define( 'WFB_URL', plugin_dir_url( __FILE__ ) );
define( 'WFB_VERSION', '1.0.0' );

/**
 * La clase principal del plugin
 */
class WhatsApp_Floating_Button {

    public function __construct() {
        // Hooks para el panel de administración
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Hooks para el frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_button' ) );
    }

    /**
     * Añadir menú en el panel de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            'WhatsApp Button',
            'WhatsApp Button',
            'manage_options',
            'whatsapp-floating-button',
            array( $this, 'settings_page_html' ),
            'dashicons-whatsapp',
            65
        );
    }

    /**
     * Registrar ajustes usando Settings API
     */
    public function register_settings() {
        register_setting( 'wfb_settings_group', 'wfb_settings', array( $this, 'sanitize_settings' ) );

        add_settings_section(
            'wfb_main_section',
            'Configuración General',
            null,
            'whatsapp-floating-button'
        );

        add_settings_field(
            'wfb_phone',
            'Número de WhatsApp',
            array( $this, 'field_phone_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_message',
            'Mensaje Predeterminado',
            array( $this, 'field_message_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_text',
            'Texto del Botón',
            array( $this, 'field_text_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_show',
            'Mostrar Botón',
            array( $this, 'field_show_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_position',
            'Posición',
            array( $this, 'field_position_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_color',
            'Color del Botón',
            array( $this, 'field_color_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_size',
            'Tamaño (px)',
            array( $this, 'field_size_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );
    }

    /**
     * Sanitizar los datos guardados
     */
    public function sanitize_settings( $input ) {
        $new_input = array();
        $new_input['phone'] = sanitize_text_field( $input['phone'] );
        $new_input['message'] = sanitize_text_field( $input['message'] );
        $new_input['text'] = sanitize_text_field( $input['text'] );
        $new_input['show'] = isset( $input['show'] ) ? 1 : 0;
        $new_input['position'] = in_array( $input['position'], array( 'right', 'left' ) ) ? $input['position'] : 'right';
        $new_input['color'] = sanitize_hex_color( $input['color'] );
        $new_input['size'] = absint( $input['size'] );

        return $new_input;
    }

    // Funciones para renderizar los campos de entrada
    public function field_phone_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['phone'] ) ? $options['phone'] : '';
        echo "<input type='text' name='wfb_settings[phone]' value='" . esc_attr( $value ) . "' placeholder='Ej: 34600000000'>";
    }

    public function field_message_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['message'] ) ? $options['message'] : '';
        echo "<input type='text' name='wfb_settings[message]' value='" . esc_attr( $value ) . "' class='regular-text'>";
    }

    public function field_text_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['text'] ) ? $options['text'] : '';
        echo "<input type='text' name='wfb_settings[text]' value='" . esc_attr( $value ) . "' placeholder='Escríbenos'>";
    }

    public function field_show_html() {
        $options = get_option( 'wfb_settings' );
        $checked = isset( $options['show'] ) && $options['show'] ? 'checked' : '';
        echo "<input type='checkbox' name='wfb_settings[show]' value='1' $checked>";
    }

    public function field_position_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['position'] ) ? $options['position'] : 'right';
        ?>
        <select name="wfb_settings[position]">
            <option value="right" <?php selected( $value, 'right' ); ?>>Derecha</option>
            <option value="left" <?php selected( $value, 'left' ); ?>>Izquierda</option>
        </select>
        <?php
    }

    public function field_color_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['color'] ) ? $options['color'] : '#25D366';
        echo "<input type='color' name='wfb_settings[color]' value='" . esc_attr( $value ) . "'>";
    }

    public function field_size_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['size'] ) ? $options['size'] : '60';
        echo "<input type='number' name='wfb_settings[size]' value='" . esc_attr( $value ) . "' min='40' max='100'>";
    }

    /**
     * Renderizar la página de ajustes
     */
    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wfb_settings_group' );
                do_settings_sections( 'whatsapp-floating-button' );
                submit_button( 'Guardar Ajustes' );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Cargar assets
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'wfb-style', WFB_URL . 'assets/css/style.css', array(), WFB_VERSION );
        wp_enqueue_script( 'wfb-script', WFB_URL . 'assets/js/script.js', array(), WFB_VERSION, true );
    }

    /**
     * Renderizar el botón en el footer
     */
    public function render_button() {
        $options = get_option( 'wfb_settings' );

        if ( ! isset( $options['show'] ) || ! $options['show'] ) {
            return;
        }

        $phone   = isset( $options['phone'] ) ? $options['phone'] : '';
        $message = isset( $options['message'] ) ? $options['message'] : '';
        $text    = isset( $options['text'] ) ? $options['text'] : '';
        $pos     = isset( $options['position'] ) ? $options['position'] : 'right';
        $color   = isset( $options['color'] ) ? $options['color'] : '#25D366';
        $size    = isset( $options['size'] ) ? $options['size'] : '60';

        if ( empty( $phone ) ) {
            return;
        }

        $url = "https://wa.me/" . preg_replace( '/[^0-9]/', '', $phone );
        if ( ! empty( $message ) ) {
            $url .= "?text=" . rawurlencode( $message );
        }

        $style = "bottom: 20px; {$pos}: 20px; background-color: {$color}; width: {$size}px; height: {$size}px;";
        ?>
        <a href="<?php echo esc_url( $url ); ?>" class="wfb-floating-button" target="_blank" rel="nofollow" style="<?php echo esc_attr( $style ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="30" height="30" fill="white">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.4-8.6-44.5-27.4-16.4-14.6-27.5-32.8-30.7-38.4-3.2-5.6-.3-8.6 2.5-11.4 2.5-2.5 5.5-6.5 8.3-9.7 2.8-3.2 3.7-5.5 5.5-9.2 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.5 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.5 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
            </svg>
            <?php if ( ! empty( $text ) ) : ?>
                <span class="wfb-text"><?php echo esc_html( $text ); ?></span>
            <?php endif; ?>
        </a>
        <?php
    }

}

// Inicializar el plugin
new WhatsApp_Floating_Button();

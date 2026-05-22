{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Vista de pie de página (Footer) institucional con enlaces legales y derechos reservados.
 * @autor          Rubén Alejandro Nolasco Ruiz
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela  
 * @mantenimiento  Ghael Garcia Manjarrez 
 * @version        1.0.0
 * @creado         11/04/2026
 * @modificado     11/04/2026
 *
 * @cambios
 * Fecha       | Autor             | Descripción
 * ------------|-------------------|------------------------------------------
 * 11/04/2026  | Rubén Alejandro   | Creación de footer institucional con gestión dinámica de año.
 * 11/04/2026  | Rubén Alejandro   | Estandarización de prólogo según manual GAMA-MPL-03.
 */
--}}
<footer class="dashboard-footer institutional-footer">
  <div class="footer-content">
    <p>Copyright © {{ now()->year }} G.A.M.A Solutions. Todos los derechos reservados.</p>
    <p class="footer-links">
      <a href="{{ route('terms') }}">Términos y condiciones</a>
      <span class="footer-link-separator">|</span>
      <a href="{{ route('privacy') }}">Aviso de privacidad</a>
    </p>
  </div>
</footer>
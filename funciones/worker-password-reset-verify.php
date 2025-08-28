<?php
require __DIR__ . '/../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
  http_response_code(400);
  exit('Token inválido.');
}

$tokenHash = hash('sha256', $token);

$sql = "SELECT id_empleado, reset_expires FROM empleado WHERE reset_token_hash = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$res = $stmt->get_result();
$emp = $res->fetch_assoc();
$stmt->close();

$valid = false;
if ($emp) {
  $now = new DateTime();
  $exp = new DateTime($emp['reset_expires']);
  if ($exp > $now) $valid = true;
}

if (!$valid) {
  http_response_code(400);
  exit('El enlace es inválido o expiró. Volvé a solicitarlo.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Restablecer contraseña - Trabajador</title>
  <link rel="stylesheet" href="/labora_db/recursos/css/styles.css">
</head>
<body>
  <div class="form-section">
    <h2>Elegí una nueva contraseña</h2>

    <form action="/labora_db/funciones/worker-password-reset-update.php" method="POST" id="reset-form">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">

      <div class="input-group">
        <label for="pass1">Nueva contraseña</label>
        <input type="password" id="pass1" name="pass1" required minlength="8" autocomplete="new-password">
        <small class="error-text" id="err-pass1"></small>
        <ul id="pw-reqs" style="margin:.5rem 0 0 1rem; font-size:.9rem; line-height:1.3;">
          <li id="req-len">Mínimo 8 caracteres</li>
          <li id="req-num">Al menos 1 número</li>
          <li id="req-spec">Al menos 1 caracter especial (p. ej. ! @ # ? *)</li>
        </ul>
      </div>

      <div class="input-group">
        <label for="pass2">Repetir contraseña</label>
        <input type="password" id="pass2" name="pass2" required autocomplete="new-password">
        <small class="error-text" id="err-pass2"></small>
      </div>

      <button type="submit">Guardar</button>
    </form>
  </div>

  <script>
  const $ = (s) => document.querySelector(s);
  function setMsg(el, msg) { el.textContent = msg || ''; }
  function passwordStrong(pw) {
    const okLen  = pw.length >= 8;
    const okNum  = /\d/.test(pw);
    const okSpec = /[^A-Za-z0-9]/.test(pw);
    return { okLen, okNum, okSpec, all: okLen && okNum && okSpec };
  }
  function paintReq(id, ok) {
    const el = document.getElementById(id);
    el.style.color = ok ? 'green' : '';
  }

  const pass1 = document.getElementById('pass1');
  const pass2 = document.getElementById('pass2');
  const err1  = document.getElementById('err-pass1');
  const err2  = document.getElementById('err-pass2');

  function validatePass1Live() {
    const pw = pass1.value || '';
    const s = passwordStrong(pw);
    paintReq('req-len',  s.okLen);
    paintReq('req-num',  s.okNum);
    paintReq('req-spec', s.okSpec);
    setMsg(err1, s.all ? '' : 'La contraseña no cumple los requisitos.');
    return s.all;
  }
  function validatePass2Live() {
    const same = pass1.value === pass2.value;
    setMsg(err2, same ? '' : 'Las contraseñas no coinciden.');
    return same;
  }

  pass1.addEventListener('input', () => {
    validatePass1Live();
    if (pass2.value) validatePass2Live();
  });
  pass2.addEventListener('input', validatePass2Live);

  document.getElementById('reset-form').addEventListener('submit', (e) => {
    const ok1 = validatePass1Live();
    const ok2 = validatePass2Live();
    if (!ok1 || !ok2) e.preventDefault();
  });
  </script>
</body>
</html>

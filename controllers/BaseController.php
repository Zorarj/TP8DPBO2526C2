<?php
/**
 * Controller dasar: membaca request (GET/POST), memanggil Model,
 * lalu menyerahkan data ke View.
 */
abstract class BaseController
{
    protected DB $model;
    protected object $view;
    protected string $page;          // nama file halaman, mis. 'index.php'
    protected string $label;         // nama entitas untuk pesan
    protected array $nullable = [];  // field yang boleh NULL jika dikosongkan

    // Data tambahan untuk dropdown relasi (diisi oleh controller turunan)
    protected function extraData(): array
    {
        return [];
    }

    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        // GET: tampilkan daftar (+ isi form bila ?edit=ID)
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $edit = isset($_GET['edit']) ? $this->model->find($_GET['edit']) : null;
        $this->view->render($this->model->all(), $edit, $this->extraData(), $flash);
    }

    private function handlePost(): void
    {
        $action = $_POST['action'] ?? '';
        $id = $_POST['id'] ?? '';
        try {
            if ($action === 'add') {
                $this->model->create($this->collect());
                $this->flash('success', "{$this->label} berhasil ditambahkan.");
            } elseif ($action === 'update') {
                $this->model->update($id, $this->collect());
                $this->flash('success', "{$this->label} berhasil diubah.");
            } elseif ($action === 'delete') {
                $this->model->delete($id);
                $this->flash('success', "{$this->label} berhasil dihapus.");
            }
        } catch (mysqli_sql_exception $e) {
            if ($action === 'delete') {
                $this->flash('danger', "{$this->label} tidak bisa dihapus.");
                $this->redirect();
            }
            // Gagal simpan: tampilkan lagi form dengan data yang tadi diisi
            $msg = $e->getCode() == 1062
                ? 'Data duplikat: nilai unik (NIDN / kode) sudah dipakai.'
                : 'Gagal menyimpan data.';
            $edit = $this->collect() + ['id' => $action === 'update' ? $id : ''];
            $this->view->render($this->model->all(), $edit, $this->extraData(),
                ['type' => 'danger', 'msg' => $msg]);
            return;
        }
        $this->redirect(); // Post-Redirect-Get
    }

    // Ambil nilai form sesuai daftar field milik model
    private function collect(): array
    {
        $data = [];
        foreach ($this->model->getFields() as $f) {
            $v = trim($_POST[$f] ?? '');
            $data[$f] = ($v === '' && in_array($f, $this->nullable, true)) ? null : $v;
        }
        return $data;
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function redirect(): void
    {
        header('Location: ' . $this->page);
        exit;
    }
}

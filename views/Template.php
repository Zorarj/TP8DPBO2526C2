<?php
/**
 * Mesin template sederhana: membaca file HTML di folder templates/,
 * mengganti placeholder DATA_xxx dengan nilai, lalu dibungkus layout.html.
 */
class Template
{
    private string $content;
    private array $map = [];

    public function __construct(string $file)
    {
        $this->content = file_get_contents(__DIR__ . '/../templates/' . $file);
    }

    public static function e($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    public function set(string $key, string $value): void
    {
        $this->map[$key] = $value;
    }

    // Satu baris <tr> untuk tabel data
    public static function tr(array $cells, string $actions): string
    {
        $html = '<tr>';
        foreach ($cells as $c) {
            $html .= '<td>' . self::e($c) . '</td>';
        }
        return $html . '<td class="text-nowrap">' . $actions . '</td></tr>';
    }

    // Tombol Edit (GET) + Delete (POST)
    public static function actions(string $page, $id): string
    {
        return '<a class="btn btn-success btn-sm" href="' . $page . '?edit=' . urlencode((string)$id) . '">Edit</a> '
            . '<form class="d-inline" method="post" action="' . $page . '" onsubmit="return confirm(\'Hapus data ini?\')">'
            . '<input type="hidden" name="action" value="delete">'
            . '<input type="hidden" name="id" value="' . self::e($id) . '">'
            . '<button class="btn btn-danger btn-sm">Delete</button></form>';
    }

    public static function emptyRow(int $cols): string
    {
        return '<tr><td colspan="' . $cols . '" class="text-center text-muted">Belum ada data.</td></tr>';
    }

    // Cetak halaman akhir (template + layout)
    public function write(string $title, ?array $flash = null): void
    {
        $alert = '';
        if ($flash) {
            $alert = '<div class="alert alert-' . self::e($flash['type']) . ' alert-dismissible fade show">'
                . self::e($flash['msg'])
                . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
        $layout = file_get_contents(__DIR__ . '/../templates/layout.html');
        // strtr = sekali jalan, sehingga data user yang mirip placeholder tidak ikut diganti
        echo strtr($layout, [
            'DATA_TITLE'   => self::e($title),
            'DATA_ALERT'   => $alert,
            'DATA_CONTENT' => strtr($this->content, $this->map),
        ]);
    }
}

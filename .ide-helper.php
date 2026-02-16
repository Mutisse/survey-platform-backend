<?php
// .ide-helper.php (na raiz do projeto)

namespace Illuminate\Http\Client {
    class Response {
        public function successful(): bool { return true; }
        public function json(): array { return []; }
        public function body(): string { return ''; }
        public function status(): int { return 200; }
        public function ok(): bool { return true; }
    }
}

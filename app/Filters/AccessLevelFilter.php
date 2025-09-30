<?php
class AccessLevelFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $nivelUsuario = session()->get('nivel');

        if (!in_array($nivelUsuario, $arguments)) {
            return redirect()->to('/sem-permissao');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}

<?php
echo posix_getpid(); // 获取当前进程的 pid

@swoole_set_process_name('swoole process master'); // 修改所在进程的进程名
sleep(100); // 模拟一个持续运行 100s 的程序, 这样就可以在进程中查看到它, 而不是运行完了就结束
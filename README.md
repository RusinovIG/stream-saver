# stream-saver
<p>Для запуска проекта локально, нужно:<br>
<ol>
<li>Склонировать репозиторий на локальную машину: git clone git@github.com:RusinovIG/stream-saver.git
<li>Установить зависимости проекта: php composer.phar install
<li>Загрузить дамп из файла dump.sql в базу mysql. 
<li>В конфигурационном файле src/Core/Config.php прописать актуальные настройки базы данных и url сервера, с которого будет сохраняться видео.
</ol>
<p>
Запуск скрипта осуществляется следующей командой:<br>
php console.php stream:save

# stream-saver
<p>Для запуска проекта локально, нужно:<br>
<ol>
<li>Склонировать репозиторий на локальную машину:<br>
git clone git@github.com:RusinovIG/stream-saver.git
<li>Установить зависимости проекта:<br>
php composer.phar install
<li>Загрузить дамп из файла dump.sql в базу mysql. 
<li>В конфигурационном файле src/Core/Config.php прописать актуальные настройки базы данных и url сервера, с которого будет сохраняться видео, абсолютный путь до папки с проектом на сервере (project_root).
<li>В конфигурационном файле src/Core/Config.php выставить требуемую длину видео-файлов секундах (video_length) и количество секунд до завершения предыдущей записи, за которое должна запуститься запись следующего файла (video_intersection_length)
</ol>
<p>
Запуск скрипта осуществляется следующей командой:<br>
php console.php stream:save

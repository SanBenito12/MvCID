<?php
// backend/modelos/VideoSimple.php

class VideoSimple
{
    /**
     * Configuración de videos relacionados a tu tema
     * Aquí defines los videos que quieres mostrar
     */
    public static function obtenerVideosTema()
{
    return [
        [
            'id' => 'WZJ2JVrOJ6M',
            'titulo' => 'Curso PHP desde Cero (Completo)',
            'descripcion' => 'Curso completo de PHP moderno desde cero en español.',
            'categoria' => 'PHP',
            'duracion' => '6:10:45',
            'fecha' => '2022-06-01'
        ],
        [
            'id' => 'z95mZVUcJ-E',
            'titulo' => 'Curso JavaScript desde Cero (Completo)',
            'descripcion' => 'Aprende JavaScript moderno desde cero con este curso completo.',
            'categoria' => 'JavaScript',
            'duracion' => '5:31:12',
            'fecha' => '2021-12-15'
        ],
        [
            'id' => 'Jyvffr3aCp0',
            'titulo' => 'Curso HTML y CSS desde cero',
            'descripcion' => 'Aprende a crear páginas web con HTML y CSS paso a paso.',
            'categoria' => 'HTML',
            'duracion' => '4:22:33',
            'fecha' => '2022-01-10'
        ],
        [
            'id' => 'hX4H1WzH-QU',
            'titulo' => 'Curso MySQL en Español | Desde cero',
            'descripcion' => 'Curso completo de base de datos MySQL desde cero.',
            'categoria' => 'Bases de Datos',
            'duracion' => '3:48:57',
            'fecha' => '2022-08-24'
        ],
        [
            'id' => 'gT0Lh1eYk78',
            'titulo' => 'PHP con MySQL | Sistema Web Paso a Paso',
            'descripcion' => 'Aprende a conectar PHP con MySQL creando un sistema web funcional.',
            'categoria' => 'PHP',
            'duracion' => '2:19:11',
            'fecha' => '2021-10-05'
        ],
        [
            'id' => 'ivdTnPl1ND0',
            'titulo' => 'Curso JavaScript Práctico con Proyecto',
            'descripcion' => 'Aprende JavaScript creando un proyecto real paso a paso.',
            'categoria' => 'JavaScript',
            'duracion' => '3:00:00',
            'fecha' => '2023-03-20'
        ]
    ];
}


    /**
     * Obtener videos por categoría
     */
    public static function obtenerPorCategoria($categoria)
    {
        $videos = self::obtenerVideosTema();
        
        if (empty($categoria)) {
            return $videos;
        }

        return array_filter($videos, function($video) use ($categoria) {
            return strtolower($video['categoria']) === strtolower($categoria);
        });
    }

    /**
     * Obtener categorías disponibles
     */
    public static function obtenerCategorias()
    {
        $videos = self::obtenerVideosTema();
        $categorias = array_unique(array_column($videos, 'categoria'));
        return $categorias;
    }

    /**
     * Buscar videos por título o descripción
     */
    public static function buscar($termino)
    {
        $videos = self::obtenerVideosTema();
        
        if (empty($termino)) {
            return $videos;
        }

        return array_filter($videos, function($video) use ($termino) {
            return stripos($video['titulo'], $termino) !== false || 
                   stripos($video['descripcion'], $termino) !== false;
        });
    }

    /**
     * Obtener video específico por ID
     */
    public static function obtenerPorId($id)
    {
        $videos = self::obtenerVideosTema();
        
        foreach ($videos as $video) {
            if ($video['id'] === $id) {
                return $video;
            }
        }
        
        return null;
    }

    /**
     * Generar URL del thumbnail
     */
    public static function getThumbnailUrl($videoId, $calidad = 'mqdefault')
    {
        // Calidades disponibles: default, mqdefault, hqdefault, sddefault, maxresdefault
        return "https://img.youtube.com/vi/{$videoId}/{$calidad}.jpg";
    }

    /**
     * Generar URL del video
     */
    public static function getVideoUrl($videoId)
    {
        return "https://www.youtube.com/watch?v={$videoId}";
    }

    /**
     * Generar URL para embed
     */
    public static function getEmbedUrl($videoId)
    {
        return "https://www.youtube.com/embed/{$videoId}";
    }
}
?>
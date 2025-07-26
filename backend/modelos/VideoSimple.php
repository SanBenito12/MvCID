<?php
// backend/modelos/VideoSimple.php

class VideoSimple
{
    private static $videos = null;

    /**
     * Configuración de videos relacionados a tu tema
     * Implementa Lazy Loading para mejor rendimiento
     */
    public static function obtenerVideosTema()
    {
        if (self::$videos === null) {
            self::$videos = [
                [
                    'id' => 'WZJ2JVrOJ6M',
                    'titulo' => 'Curso PHP desde Cero (Completo)',
                    'descripcion' => 'Curso completo de PHP moderno desde cero en español. Aprende programación orientada a objetos, MVC y buenas prácticas.',
                    'categoria' => 'PHP',
                    'duracion' => '6:10:45',
                    'fecha' => '2022-06-01',
                    'nivel' => 'Principiante',
                    'tags' => ['php', 'programacion', 'web', 'backend']
                ],
                [
                    'id' => 'z95mZVUcJ-E',
                    'titulo' => 'Curso JavaScript desde Cero (Completo)',
                    'descripcion' => 'Aprende JavaScript moderno desde cero con este curso completo. ES6+, DOM, Async/Await y más.',
                    'categoria' => 'JavaScript',
                    'duracion' => '5:31:12',
                    'fecha' => '2021-12-15',
                    'nivel' => 'Principiante',
                    'tags' => ['javascript', 'programacion', 'web', 'frontend']
                ],
                [
                    'id' => 'Jyvffr3aCp0',
                    'titulo' => 'Curso HTML y CSS desde cero',
                    'descripcion' => 'Aprende a crear páginas web con HTML y CSS paso a paso. Flexbox, Grid, Responsive Design.',
                    'categoria' => 'HTML',
                    'duracion' => '4:22:33',
                    'fecha' => '2022-01-10',
                    'nivel' => 'Principiante',
                    'tags' => ['html', 'css', 'web', 'frontend', 'responsive']
                ],
                [
                    'id' => 'hX4H1WzH-QU',
                    'titulo' => 'Curso MySQL en Español | Desde cero',
                    'descripcion' => 'Curso completo de base de datos MySQL desde cero. Consultas, índices, procedimientos y optimización.',
                    'categoria' => 'Bases de Datos',
                    'duracion' => '3:48:57',
                    'fecha' => '2022-08-24',
                    'nivel' => 'Intermedio',
                    'tags' => ['mysql', 'base-de-datos', 'sql', 'backend']
                ],
                [
                    'id' => 'gT0Lh1eYk78',
                    'titulo' => 'PHP con MySQL | Sistema Web Paso a Paso',
                    'descripcion' => 'Aprende a conectar PHP con MySQL creando un sistema web funcional. CRUD completo y autenticación.',
                    'categoria' => 'PHP',
                    'duracion' => '2:19:11',
                    'fecha' => '2021-10-05',
                    'nivel' => 'Intermedio',
                    'tags' => ['php', 'mysql', 'crud', 'autenticacion', 'web']
                ],
                [
                    'id' => 'ivdTnPl1ND0',
                    'titulo' => 'Curso JavaScript Práctico con Proyecto',
                    'descripcion' => 'Aprende JavaScript creando un proyecto real paso a paso. Manipulación del DOM y eventos.',
                    'categoria' => 'JavaScript',
                    'duracion' => '3:00:00',
                    'fecha' => '2023-03-20',
                    'nivel' => 'Intermedio',
                    'tags' => ['javascript', 'proyecto', 'dom', 'eventos', 'practica']
                ],
                [
                    'id' => 'dQw4w9WgXcQ',
                    'titulo' => 'Curso React JS desde Cero',
                    'descripcion' => 'Aprende React desde cero. Componentes, Hooks, Context API y desarrollo de aplicaciones modernas.',
                    'categoria' => 'JavaScript',
                    'duracion' => '4:15:30',
                    'fecha' => '2023-05-15',
                    'nivel' => 'Avanzado',
                    'tags' => ['react', 'javascript', 'frontend', 'spa', 'hooks']
                ],
                [
                    'id' => 'MtCrVQX8-PQ',
                    'titulo' => 'API REST con PHP y MySQL',
                    'descripcion' => 'Crea una API REST profesional con PHP y MySQL. JWT, validaciones y documentación.',
                    'categoria' => 'PHP',
                    'duracion' => '2:45:20',
                    'fecha' => '2023-07-10',
                    'nivel' => 'Avanzado',
                    'tags' => ['api', 'rest', 'php', 'jwt', 'backend']
                ]
            ];
        }

        return self::$videos;
    }

    /**
     * Obtener videos por categoría con validación
     */
    public static function obtenerPorCategoria($categoria)
    {
        $videos = self::obtenerVideosTema();
        
        if (empty($categoria)) {
            return $videos;
        }

        // Validar que la categoría existe
        $categoriasValidas = self::obtenerCategorias();
        if (!in_array($categoria, $categoriasValidas)) {
            throw new InvalidArgumentException("Categoría '{$categoria}' no válida");
        }

        return array_values(array_filter($videos, function($video) use ($categoria) {
            return strcasecmp($video['categoria'], $categoria) === 0;
        }));
    }

    /**
     * Obtener videos por nivel
     */
    public static function obtenerPorNivel($nivel)
    {
        $videos = self::obtenerVideosTema();
        
        if (empty($nivel)) {
            return $videos;
        }

        return array_values(array_filter($videos, function($video) use ($nivel) {
            return strcasecmp($video['nivel'], $nivel) === 0;
        }));
    }

    /**
     * Obtener categorías disponibles
     */
    public static function obtenerCategorias()
    {
        $videos = self::obtenerVideosTema();
        $categorias = array_unique(array_column($videos, 'categoria'));
        sort($categorias);
        return $categorias;
    }

    /**
     * Obtener niveles disponibles
     */
    public static function obtenerNiveles()
    {
        $videos = self::obtenerVideosTema();
        $niveles = array_unique(array_column($videos, 'nivel'));
        
        // Ordenar por dificultad
        $ordenNiveles = ['Principiante', 'Intermedio', 'Avanzado'];
        $nivelesOrdenados = [];
        
        foreach ($ordenNiveles as $nivel) {
            if (in_array($nivel, $niveles)) {
                $nivelesOrdenados[] = $nivel;
            }
        }
        
        return $nivelesOrdenados;
    }

    /**
     * Buscar videos por múltiples criterios
     */
    public static function buscar($termino, $filtros = [])
    {
        $videos = self::obtenerVideosTema();
        
        if (empty($termino) && empty($filtros)) {
            return $videos;
        }

        $resultados = array_filter($videos, function($video) use ($termino, $filtros) {
            $coincideTermino = true;
            $cumpleFiltros = true;

            // Búsqueda por término
            if (!empty($termino)) {
                $termino = strtolower($termino);
                $coincideTermino = 
                    stripos($video['titulo'], $termino) !== false ||
                    stripos($video['descripcion'], $termino) !== false ||
                    (isset($video['tags']) && 
                     count(array_filter($video['tags'], function($tag) use ($termino) {
                         return stripos($tag, $termino) !== false;
                     })) > 0);
            }

            // Aplicar filtros adicionales
            if (!empty($filtros)) {
                if (isset($filtros['categoria']) && !empty($filtros['categoria'])) {
                    $cumpleFiltros = $cumpleFiltros && 
                        strcasecmp($video['categoria'], $filtros['categoria']) === 0;
                }

                if (isset($filtros['nivel']) && !empty($filtros['nivel'])) {
                    $cumpleFiltros = $cumpleFiltros && 
                        strcasecmp($video['nivel'], $filtros['nivel']) === 0;
                }

                if (isset($filtros['duracion_min']) && !empty($filtros['duracion_min'])) {
                    $duracionSegundos = self::convertirDuracionASegundos($video['duracion']);
                    $cumpleFiltros = $cumpleFiltros && 
                        $duracionSegundos >= $filtros['duracion_min'];
                }
            }

            return $coincideTermino && $cumpleFiltros;
        });

        return array_values($resultados);
    }

    /**
     * Obtener video específico por ID con validación
     */
    public static function obtenerPorId($id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("ID de video requerido");
        }

        $videos = self::obtenerVideosTema();
        
        foreach ($videos as $video) {
            if ($video['id'] === $id) {
                return $video;
            }
        }
        
        return null;
    }

    /**
     * Generar URL del thumbnail con calidades múltiples
     */
    public static function getThumbnailUrl($videoId, $calidad = 'mqdefault')
    {
        if (empty($videoId)) {
            throw new InvalidArgumentException("ID de video requerido");
        }

        $calidadesValidas = [
            'default', 'mqdefault', 'hqdefault', 
            'sddefault', 'maxresdefault'
        ];

        if (!in_array($calidad, $calidadesValidas)) {
            $calidad = 'mqdefault'; // Fallback
        }

        return "https://img.youtube.com/vi/{$videoId}/{$calidad}.jpg";
    }

    /**
     * Obtener múltiples calidades de thumbnail
     */
    public static function getThumbnailUrls($videoId)
    {
        return [
            'default' => self::getThumbnailUrl($videoId, 'default'),
            'medium' => self::getThumbnailUrl($videoId, 'mqdefault'),
            'high' => self::getThumbnailUrl($videoId, 'hqdefault'),
            'standard' => self::getThumbnailUrl($videoId, 'sddefault'),
            'maxres' => self::getThumbnailUrl($videoId, 'maxresdefault')
        ];
    }

    /**
     * Generar URL del video
     */
    public static function getVideoUrl($videoId)
    {
        if (empty($videoId)) {
            throw new InvalidArgumentException("ID de video requerido");
        }

        return "https://www.youtube.com/watch?v={$videoId}";
    }

    /**
     * Generar URL para embed con parámetros personalizables
     */
    public static function getEmbedUrl($videoId, $parametros = [])
    {
        if (empty($videoId)) {
            throw new InvalidArgumentException("ID de video requerido");
        }

        $url = "https://www.youtube.com/embed/{$videoId}";
        
        // Parámetros por defecto
        $parametrosDefecto = [
            'autoplay' => 0,
            'controls' => 1,
            'showinfo' => 0,
            'rel' => 0,
            'modestbranding' => 1,
            'iv_load_policy' => 3
        ];

        $parametrosFinales = array_merge($parametrosDefecto, $parametros);
        
        if (!empty($parametrosFinales)) {
            $url .= '?' . http_build_query($parametrosFinales);
        }

        return $url;
    }

    /**
     * Obtener estadísticas básicas
     */
    public static function obtenerEstadisticas()
    {
        $videos = self::obtenerVideosTema();
        
        return [
            'total_videos' => count($videos),
            'por_categoria' => self::contarPorCategoria(),
            'por_nivel' => self::contarPorNivel(),
            'duracion_total' => self::calcularDuracionTotal(),
            'video_mas_reciente' => self::obtenerVideoMasReciente(),
            'video_mas_antiguo' => self::obtenerVideoMasAntiguo()
        ];
    }

    /**
     * Métodos de utilidad privados
     */
    private static function contarPorCategoria()
    {
        $videos = self::obtenerVideosTema();
        $conteo = [];
        
        foreach ($videos as $video) {
            $categoria = $video['categoria'];
            $conteo[$categoria] = ($conteo[$categoria] ?? 0) + 1;
        }
        
        return $conteo;
    }

    private static function contarPorNivel()
    {
        $videos = self::obtenerVideosTema();
        $conteo = [];
        
        foreach ($videos as $video) {
            $nivel = $video['nivel'];
            $conteo[$nivel] = ($conteo[$nivel] ?? 0) + 1;
        }
        
        return $conteo;
    }

    private static function calcularDuracionTotal()
    {
        $videos = self::obtenerVideosTema();
        $totalSegundos = 0;
        
        foreach ($videos as $video) {
            $totalSegundos += self::convertirDuracionASegundos($video['duracion']);
        }
        
        return self::convertirSegundosADuracion($totalSegundos);
    }

    private static function obtenerVideoMasReciente()
    {
        $videos = self::obtenerVideosTema();
        
        usort($videos, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
        
        return $videos[0] ?? null;
    }

    private static function obtenerVideoMasAntiguo()
    {
        $videos = self::obtenerVideosTema();
        
        usort($videos, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });
        
        return $videos[0] ?? null;
    }

    private static function convertirDuracionASegundos($duracion)
    {
        $partes = explode(':', $duracion);
        $segundos = 0;
        
        if (count($partes) >= 3) { // H:M:S
            $segundos += intval($partes[0]) * 3600; // Horas
            $segundos += intval($partes[1]) * 60;   // Minutos
            $segundos += intval($partes[2]);        // Segundos
        } elseif (count($partes) == 2) { // M:S
            $segundos += intval($partes[0]) * 60;   // Minutos
            $segundos += intval($partes[1]);        // Segundos
        }
        
        return $segundos;
    }

    private static function convertirSegundosADuracion($totalSegundos)
    {
        $horas = floor($totalSegundos / 3600);
        $minutos = floor(($totalSegundos % 3600) / 60);
        $segundos = $totalSegundos % 60;
        
        return sprintf('%d:%02d:%02d', $horas, $minutos, $segundos);
    }
}
?>
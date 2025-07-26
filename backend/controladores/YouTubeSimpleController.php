<?php
// backend/controladores/YouTubeSimpleController.php

require_once __DIR__ . '/../modelos/VideoSimple.php';

class YouTubeSimpleController
{
    private $videoModel;

    public function __construct()
    {
        $this->videoModel = new VideoSimple();
    }

    /**
     * Obtener todos los videos con datos para YouTube Player API
     */
    public function obtenerTodos()
    {
        try {
            $videos = $this->videoModel->obtenerVideosTema();
            
            // Enriquecer videos con datos para YouTube Player API
            $videosEnriquecidos = $this->enriquecerVideosParaPlayer($videos);
            
            return [
                'success' => true,
                'videos' => $videosEnriquecidos,
                'total' => count($videosEnriquecidos),
                'player_config' => $this->getPlayerConfiguration()
            ];
            
        } catch (Exception $e) {
            return $this->handleError('Error al obtener videos', $e);
        }
    }

    /**
     * Obtener videos por categoría
     */
    public function obtenerPorCategoria($categoria = '')
    {
        try {
            $videos = $this->videoModel->obtenerPorCategoria($categoria);
            $videosEnriquecidos = $this->enriquecerVideosParaPlayer($videos);
            
            return [
                'success' => true,
                'videos' => $videosEnriquecidos,
                'categoria' => $categoria,
                'total' => count($videosEnriquecidos),
                'player_config' => $this->getPlayerConfiguration()
            ];
            
        } catch (Exception $e) {
            return $this->handleError('Error al obtener videos por categoría', $e);
        }
    }

    /**
     * Buscar videos
     */
    public function buscar($termino = '')
    {
        try {
            $videos = $this->videoModel->buscar($termino);
            $videosEnriquecidos = $this->enriquecerVideosParaPlayer($videos);
            
            return [
                'success' => true,
                'videos' => $videosEnriquecidos,
                'termino' => $termino,
                'total' => count($videosEnriquecidos),
                'player_config' => $this->getPlayerConfiguration()
            ];
            
        } catch (Exception $e) {
            return $this->handleError('Error en la búsqueda', $e);
        }
    }

    /**
     * Obtener categorías disponibles
     */
    public function obtenerCategorias()
    {
        try {
            $categorias = $this->videoModel->obtenerCategorias();
            
            return [
                'success' => true,
                'categorias' => $categorias
            ];
            
        } catch (Exception $e) {
            return $this->handleError('Error al obtener categorías', $e);
        }
    }

    /**
     * Obtener video específico con configuración completa para player
     */
    public function obtenerVideo($id)
    {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'error' => 'ID de video requerido'
                ];
            }

            $video = $this->videoModel->obtenerPorId($id);
            
            if (!$video) {
                return [
                    'success' => false,
                    'error' => 'Video no encontrado'
                ];
            }

            // Enriquecer video individual para player
            $videoEnriquecido = $this->enriquecerVideoParaPlayer($video);
            
            return [
                'success' => true,
                'video' => $videoEnriquecido,
                'player_config' => $this->getPlayerConfiguration(),
                'suggested_videos' => $this->obtenerVideosSugeridos($video['categoria'], $id)
            ];
            
        } catch (Exception $e) {
            return $this->handleError('Error al obtener video', $e);
        }
    }

    /**
     * Obtener configuración del player para YouTube IFrame API
     */
    public function obtenerConfiguracionPlayer()
    {
        try {
            return [
                'success' => true,
                'config' => $this->getPlayerConfiguration(),
                'eventos' => $this->getPlayerEvents(),
                'parametros' => $this->getPlayerParams()
            ];
        } catch (Exception $e) {
            return $this->handleError('Error al obtener configuración del player', $e);
        }
    }

    /**
     * Enriquecer videos con datos necesarios para YouTube Player API
     */
    private function enriquecerVideosParaPlayer($videos)
    {
        return array_map([$this, 'enriquecerVideoParaPlayer'], $videos);
    }

    /**
     * Enriquecer video individual con datos para player
     */
    private function enriquecerVideoParaPlayer($video)
    {
        return [
            'id' => $video['id'],
            'titulo' => $video['titulo'],
            'descripcion' => $video['descripcion'],
            'categoria' => $video['categoria'],
            'duracion' => $video['duracion'],
            'fecha' => $video['fecha'],
            'urls' => [
                'thumbnail' => $this->videoModel->getThumbnailUrl($video['id']),
                'thumbnail_hq' => $this->videoModel->getThumbnailUrl($video['id'], 'hqdefault'),
                'video' => $this->videoModel->getVideoUrl($video['id']),
                'embed' => $this->videoModel->getEmbedUrl($video['id'])
            ],
            'player_data' => [
                'video_id' => $video['id'],
                'autoplay' => 0,
                'controls' => 1,
                'showinfo' => 1,
                'rel' => 0,
                'modestbranding' => 1,
                'iv_load_policy' => 3
            ]
        ];
    }

    /**
     * Configuración por defecto del YouTube Player
     */
    private function getPlayerConfiguration()
    {
        return [
            'height' => '390',
            'width' => '640',
            'videoId' => '',
            'playerVars' => [
                'autoplay' => 0,
                'controls' => 1,
                'showinfo' => 1,
                'rel' => 0,
                'modestbranding' => 1,
                'iv_load_policy' => 3,
                'fs' => 1,
                'cc_load_policy' => 0,
                'start' => 0,
                'end' => 0
            ],
            'events' => [
                'onReady' => 'onPlayerReady',
                'onStateChange' => 'onPlayerStateChange',
                'onError' => 'onPlayerError'
            ]
        ];
    }

    /**
     * Eventos disponibles del player
     */
    private function getPlayerEvents()
    {
        return [
            'onReady' => 'Se ejecuta cuando el player está listo',
            'onStateChange' => 'Se ejecuta cuando cambia el estado del video',
            'onPlaybackQualityChange' => 'Se ejecuta cuando cambia la calidad',
            'onPlaybackRateChange' => 'Se ejecuta cuando cambia la velocidad',
            'onError' => 'Se ejecuta cuando hay un error',
            'onApiChange' => 'Se ejecuta cuando la API cambia'
        ];
    }

    /**
     * Parámetros disponibles del player
     */
    private function getPlayerParams()
    {
        return [
            'autoplay' => 'Reproducción automática (0 o 1)',
            'controls' => 'Mostrar controles (0, 1 o 2)',
            'showinfo' => 'Mostrar información del video (0 o 1)',
            'rel' => 'Mostrar videos relacionados (0 o 1)',
            'modestbranding' => 'Branding modesto (0 o 1)',
            'iv_load_policy' => 'Política de anotaciones (1 o 3)',
            'fs' => 'Permitir pantalla completa (0 o 1)',
            'cc_load_policy' => 'Mostrar subtítulos (0 o 1)',
            'start' => 'Tiempo de inicio en segundos',
            'end' => 'Tiempo de fin en segundos'
        ];
    }

    /**
     * Obtener videos sugeridos basados en categoría
     */
    private function obtenerVideosSugeridos($categoria, $videoIdActual, $limite = 4)
    {
        $videos = $this->videoModel->obtenerPorCategoria($categoria);
        
        // Filtrar el video actual
        $videos = array_filter($videos, function($video) use ($videoIdActual) {
            return $video['id'] !== $videoIdActual;
        });

        // Limitar resultados
        $videos = array_slice($videos, 0, $limite);
        
        return $this->enriquecerVideosParaPlayer($videos);
    }

    /**
     * Manejo centralizado de errores
     */
    private function handleError($mensaje, Exception $e)
    {
        error_log("YouTubeSimpleController Error: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $mensaje . ': ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
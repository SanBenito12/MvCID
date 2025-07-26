<?php
// backend/controladores/YouTubeSimpleController.php

require_once __DIR__ . '/../modelos/VideoSimple.php';

class YouTubeSimpleController
{
    /**
     * Obtener todos los videos
     */
    public function obtenerTodos()
    {
        try {
            $videos = VideoSimple::obtenerVideosTema();
            
            return [
                'success' => true,
                'videos' => $videos,
                'total' => count($videos)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener videos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener videos por categoría
     */
    public function obtenerPorCategoria($categoria = '')
    {
        try {
            $videos = VideoSimple::obtenerPorCategoria($categoria);
            
            return [
                'success' => true,
                'videos' => $videos,
                'categoria' => $categoria,
                'total' => count($videos)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener videos por categoría: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar videos
     */
    public function buscar($termino = '')
    {
        try {
            $videos = VideoSimple::buscar($termino);
            
            return [
                'success' => true,
                'videos' => $videos,
                'termino' => $termino,
                'total' => count($videos)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en la búsqueda: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener categorías disponibles
     */
    public function obtenerCategorias()
    {
        try {
            $categorias = VideoSimple::obtenerCategorias();
            
            return [
                'success' => true,
                'categorias' => $categorias
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener categorías: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener video específico
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

            $video = VideoSimple::obtenerPorId($id);
            
            if (!$video) {
                return [
                    'success' => false,
                    'error' => 'Video no encontrado'
                ];
            }
            
            return [
                'success' => true,
                'video' => $video
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener video: ' . $e->getMessage()
            ];
        }
    }
}
?>
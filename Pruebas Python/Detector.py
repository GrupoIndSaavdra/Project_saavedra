import cv2
import time
import logging
from dataclasses import dataclass
from typing import Tuple

@dataclass
class DetectorConfig:
    """Configuración del detector facial"""
    scale_factor: float = 1.1
    min_neighbors: int = 5
    min_size: Tuple[int, int] = (80, 80)
    resize_factor: float = 0.75
    camera_index: int = 0
    auto_resize: bool = True
    max_window_width: int = 1200
    max_window_height: int = 800

class FaceDetector:
    """Detector facial optimizado con Haar Cascades"""
    
    def __init__(self, config: DetectorConfig = None):
        self.config = config or DetectorConfig()
        self.detector = self._load_detector()
        self.camera = None
        self.window_name = "Detector Facial - Presiona 'q' para salir"
        
    def _load_detector(self) -> cv2.CascadeClassifier:
        """Carga el clasificador Haar"""
        detector_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        detector = cv2.CascadeClassifier(detector_path)
        
        if detector.empty():
            raise RuntimeError(f"No se pudo cargar el clasificador desde: {detector_path}")
        
        return detector
    
    def _init_camera(self) -> cv2.VideoCapture:
        """Inicializa la cámara con configuración optimizada"""
        cam = cv2.VideoCapture(self.config.camera_index)
        
        if not cam.isOpened():
            raise RuntimeError(f"No se pudo abrir la cámara {self.config.camera_index}")
        
        # Configurar propiedades de la cámara para mejor rendimiento
        cam.set(cv2.CAP_PROP_BUFFERSIZE, 1)
        cam.set(cv2.CAP_PROP_FPS, 30)
        
        return cam
    
    def detect_faces(self, frame):
        """Detecta rostros en el frame dado"""
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        gray = cv2.equalizeHist(gray)
        
        return self.detector.detectMultiScale(
            gray,
            scaleFactor=self.config.scale_factor,
            minNeighbors=self.config.min_neighbors,
            minSize=self.config.min_size,
            flags=cv2.CASCADE_SCALE_IMAGE
        )
    
    def draw_detections(self, frame, faces):
        """Dibuja rectángulos alrededor de los rostros detectados"""
        for (x, y, w, h) in faces:
            # Rectángulo principal
            cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)
            
            # Etiqueta con confianza
            cv2.putText(
                frame, 
                "Rostro", 
                (x, y - 10), 
                cv2.FONT_HERSHEY_SIMPLEX, 
                0.6, 
                (0, 255, 0), 
                2
            )
    
    def _auto_resize_window(self, frame):
        """Ajusta automáticamente el tamaño de la ventana según la pantalla"""
        if not self.config.auto_resize:
            return frame
            
        h, w = frame.shape[:2]
        
        # Calcular factor de escala para ajustar a los límites máximos
        scale_w = self.config.max_window_width / w if w > self.config.max_window_width else 1.0
        scale_h = self.config.max_window_height / h if h > self.config.max_window_height else 1.0
        scale = min(scale_w, scale_h)
        
        if scale < 1.0:
            new_w = int(w * scale)
            new_h = int(h * scale)
            frame = cv2.resize(frame, (new_w, new_h))
            
        return frame
    
    def run(self):
        """Ejecuta el detector en tiempo real"""
        try:
            self.camera = self._init_camera()
            fps_counter = 0
            fps_start_time = time.time()
            
            # Crear ventana con tamaño ajustable
            cv2.namedWindow(self.window_name, cv2.WINDOW_NORMAL)
            
            print("Detector iniciado. Presiona 'q' o ESC para salir.")
            
            while True:
                frame_start = time.time()
                
                ret, frame = self.camera.read()
                if not ret:
                    logging.warning("No se pudo leer el frame de la cámara")
                    continue
                
                # Redimensionar para mejor rendimiento
                if self.config.resize_factor != 1.0:
                    frame = cv2.resize(
                        frame, 
                        None, 
                        fx=self.config.resize_factor, 
                        fy=self.config.resize_factor
                    )
                
                # Detectar rostros
                faces = self.detect_faces(frame)
                
                # Dibujar detecciones
                self.draw_detections(frame, faces)
                
                # Calcular FPS cada segundo
                fps_counter += 1
                current_time = time.time()
                
                if current_time - fps_start_time >= 1.0:
                    fps = fps_counter / (current_time - fps_start_time)
                    fps_counter = 0
                    fps_start_time = current_time
                else:
                    fps = 1 / (current_time - frame_start) if current_time > frame_start else 0
                
                # Ajustar tamaño de ventana automáticamente
                display_frame = self._auto_resize_window(frame)
                
                # Mostrar información
                info_text = f"FPS: {int(fps)} | Rostros: {len(faces)}"
                cv2.putText(
                    display_frame,
                    info_text,
                    (10, 30),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.7,
                    (0, 255, 255),
                    2
                )
                
                cv2.imshow(self.window_name, display_frame)
                
                # Control de salida
                key = cv2.waitKey(1) & 0xFF
                if key == ord('q') or key == 27:  # 'q' o ESC
                    break
                    
        except KeyboardInterrupt:
            print("\nInterrumpido por el usuario")
        except Exception as e:
            logging.error(f"Error durante la ejecución: {e}")
            raise
        finally:
            self._cleanup()
    
    def _cleanup(self):
        """Libera recursos"""
        if self.camera:
            self.camera.release()
        cv2.destroyAllWindows()
        print("Recursos liberados correctamente")

def main():
    """Función principal - Ejemplo de uso del detector"""
    try:
        # Configuración personalizada (opcional)
        config = DetectorConfig(
            scale_factor=1.1,
            min_neighbors=5,
            min_size=(60, 60),  # Detectar rostros más pequeños
            resize_factor=0.75,
            camera_index=0
        )
        
        # Crear y ejecutar detector
        detector = FaceDetector(config)
        detector.run()
        
    except Exception as e:
        logging.error(f"Error en main: {e}")
        print(f"Error: {e}")
        return 1
    
    return 0

if __name__ == "__main__":
    main()

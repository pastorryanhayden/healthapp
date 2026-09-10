import Flutter
import UIKit
import WidgetKit

class SceneDelegate: FlutterSceneDelegate {
  override func sceneDidBecomeActive(_ scene: UIScene) {
    super.sceneDidBecomeActive(scene)
    WidgetCenter.shared.reloadTimelines(ofKind: "CaloriesWidget")
  }

  override func sceneWillResignActive(_ scene: UIScene) {
    WidgetCenter.shared.reloadTimelines(ofKind: "CaloriesWidget")
    super.sceneWillResignActive(scene)
  }
}

import './widgets/comments-widget';
import './widgets/login-widget';
import './widgets/push-widget';
import './widgets/status-widget';

import { getCoreConfig, requireCoreConfig } from './config';
import { registerCoreServiceWorker, subscribeToCorePush } from './push';

export { buildAuthUrl, createSilentCheckFrame, openPopupLogin } from './auth';
export {
  getCoreConfig,
  pushContextFor,
  requireCoreConfig,
  serviceWorkerScopeFor,
  serviceWorkerUrlFor,
} from './config';
export {
  pushPayloadFor,
  registerCoreServiceWorker,
  sendPushSubscription,
  subscribeToCorePush,
} from './push';
export type {
  AuthFlowOptions,
  CoreWidgetStatus,
  PushSubscriptionContext,
  PushSubscriptionPayload,
  StarAtlasCoreConfig,
} from './types';

export const StarAtlasCoreSdk = {
  getConfig: getCoreConfig,
  requireConfig: requireCoreConfig,
  registerServiceWorker: registerCoreServiceWorker,
  subscribeToPush: subscribeToCorePush,
};

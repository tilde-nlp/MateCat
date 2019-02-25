import { group } from 'k6';
import { get, checkOk, msleep } from './utils.js';

export const options = {
    vus: 3,
    duration: '5s'
};

export default function() {
  group('User', () => {
    const result = get('profile');
    checkOk(result);
    msleep(1);
  });
};

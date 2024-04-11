# Use the specific version of Node.js as the base image
# version 20 end of life : 2026-04-30
FROM node:20

RUN apt-get update && apt-get install -y nodejs && apt-get install -y git && apt-get clean

CMD npm install && npm run watch

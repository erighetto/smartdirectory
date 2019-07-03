#!/usr/bin/env bash
export AWS_PROFILE="ema" && export AWS_REGION=eu-central-1
cd dist
aws s3 sync . s3://webconsulenza.com --delete
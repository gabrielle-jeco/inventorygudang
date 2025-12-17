// ============================================
// 🤖 JENKINS PIPELINE - CI/CD untuk Inventory Gudang
// ============================================
// File ini adalah "resep" untuk Jenkins
// Jenkins akan membaca file ini dan menjalankan pipeline

pipeline {
    // Agent (tempat Jenkins menjalankan pipeline)
    agent any
    
    // Environment variables (variabel yang dipakai di seluruh pipeline)
    environment {
        // Project ID Google Cloud
        GCP_PROJECT_ID = 'your-project-id'  // Ganti dengan project ID kamu
        
        // Image name
        IMAGE_NAME = 'inventorygudang'
        IMAGE_TAG = "${env.BUILD_NUMBER}"  // Tag dengan build number
        GCR_IMAGE = "gcr.io/${GCP_PROJECT_ID}/${IMAGE_NAME}:${IMAGE_TAG}"
        GCR_IMAGE_LATEST = "gcr.io/${GCP_PROJECT_ID}/${IMAGE_NAME}:latest"
        
        // GKE Configuration
        GKE_CLUSTER_NAME = 'inventorygudang-cluster'
        GKE_ZONE = 'asia-southeast2'
        GKE_NAMESPACE = 'inventorygudang'
        
        // Docker registry
        DOCKER_REGISTRY = "gcr.io"
    }
    
    // Options (konfigurasi pipeline)
    options {
        // Timeout (jika lebih dari 30 menit, cancel)
        timeout(time: 30, unit: 'MINUTES')
        
        // Build number display
        buildDiscarder(logRotator(numToKeepStr: '10'))  // Keep 10 build terakhir
        
        // AnsiColor (warna di console output)
        ansiColor('xterm')
    }
    
    // Stages (tahapan pipeline)
    stages {
        // ============================================
        // STAGE 1: CHECKOUT - Ambil kode dari Git
        // ============================================
        stage('Checkout') {
            steps {
                echo '📥 Checking out code from Git...'
                checkout scm
                
                // Tampilkan info commit
                script {
                    env.GIT_COMMIT = sh(
                        script: 'git rev-parse --short HEAD',
                        returnStdout: true
                    ).trim()
                    env.GIT_BRANCH = env.BRANCH_NAME
                    echo "Branch: ${env.GIT_BRANCH}"
                    echo "Commit: ${env.GIT_COMMIT}"
                }
            }
        }
        
        // ============================================
        // STAGE 2: TEST - Test kode aplikasi
        // ============================================
        stage('Test') {
            steps {
                echo '🧪 Running tests...'
                
                // Test dengan Docker (isolated environment)
                sh '''
                    echo "Installing dependencies..."
                    docker run --rm -v $(pwd):/app -w /app \
                        composer:2 composer install --no-interaction --prefer-dist
                    
                    echo "Running PHPUnit tests..."
                    docker run --rm -v $(pwd):/app -w /app \
                        php:8.2-cli ./vendor/bin/phpunit || true
                    
                    # Atau pakai Pest jika ada
                    # docker run --rm -v $(pwd):/app -w /app \
                    #     php:8.2-cli ./vendor/bin/pest || true
                '''
            }
            
            // Post actions (setelah stage selesai)
            post {
                // Jika test gagal, kirim notifikasi
                failure {
                    echo '❌ Tests failed!'
                    // Bisa tambahkan notifikasi Slack/Email di sini
                }
                success {
                    echo '✅ All tests passed!'
                }
            }
        }
        
        // ============================================
        // STAGE 3: BUILD - Build Docker image
        // ============================================
        stage('Build Docker Image') {
            steps {
                echo '🐳 Building Docker image...'
                
                script {
                    // Authenticate dengan Google Cloud
                    sh '''
                        gcloud auth configure-docker ${DOCKER_REGISTRY} --quiet
                    '''
                    
                    // Build Docker image
                    sh """
                        docker build -t ${GCR_IMAGE} .
                        docker tag ${GCR_IMAGE} ${GCR_IMAGE_LATEST}
                    """
                    
                    echo "✅ Image built: ${GCR_IMAGE}"
                }
            }
        }
        
        // ============================================
        // STAGE 4: PUSH - Push image ke GCR
        // ============================================
        stage('Push to GCR') {
            steps {
                echo '📤 Pushing image to Google Container Registry...'
                
                script {
                    // Push image dengan tag version
                    sh """
                        docker push ${GCR_IMAGE}
                        docker push ${GCR_IMAGE_LATEST}
                    """
                    
                    echo "✅ Image pushed: ${GCR_IMAGE}"
                }
            }
        }
        
        // ============================================
        // STAGE 5: DEPLOY - Deploy ke GKE
        // ============================================
        stage('Deploy to GKE') {
            when {
                // Hanya deploy jika di branch main/master
                anyOf {
                    branch 'main'
                    branch 'master'
                }
            }
            
            steps {
                echo '🚀 Deploying to GKE...'
                
                script {
                    // Setup kubectl
                    sh """
                        gcloud container clusters get-credentials ${GKE_CLUSTER_NAME} \
                            --zone ${GKE_ZONE} \
                            --project ${GCP_PROJECT_ID}
                    """
                    
                    // Update deployment dengan image baru
                    sh """
                        kubectl set image deployment/inventorygudang-app \
                            inventorygudang=${GCR_IMAGE} \
                            -n ${GKE_NAMESPACE}
                    """
                    
                    // Wait for rollout
                    sh """
                        kubectl rollout status deployment/inventorygudang-app \
                            -n ${GKE_NAMESPACE} \
                            --timeout=5m
                    """
                    
                    echo "✅ Deployment successful!"
                }
            }
            
            post {
                // Jika deploy gagal, rollback
                failure {
                    echo '❌ Deployment failed! Rolling back...'
                    sh """
                        kubectl rollout undo deployment/inventorygudang-app \
                            -n ${GKE_NAMESPACE}
                    """
                }
                success {
                    echo '✅ Deployment successful!'
                }
            }
        }
        
        // ============================================
        // STAGE 6: VERIFY - Verifikasi deployment
        // ============================================
        stage('Verify Deployment') {
            when {
                anyOf {
                    branch 'main'
                    branch 'master'
                }
            }
            
            steps {
                echo '✅ Verifying deployment...'
                
                script {
                    // Cek pod status
                    sh """
                        kubectl get pods -n ${GKE_NAMESPACE} -l app=inventorygudang
                    """
                    
                    // Health check
                    sh """
                        kubectl wait --for=condition=ready pod \
                            -l app=inventorygudang \
                            -n ${GKE_NAMESPACE} \
                            --timeout=2m
                    """
                    
                    echo "✅ All pods are ready!"
                }
            }
        }
    }
    
    // Post actions (setelah semua stage selesai)
    post {
        // Selalu jalankan (success atau failure)
        always {
            echo '🧹 Cleaning up...'
            
            // Cleanup Docker images lokal (hemat space)
            sh '''
                docker system prune -f || true
            '''
            
            // Archive artifacts (jika ada)
            archiveArtifacts artifacts: '**/*.log', allowEmptyArchive: true
        }
        
        // Jika berhasil
        success {
            echo '🎉 Pipeline completed successfully!'
            
            // Bisa tambahkan notifikasi di sini
            // slackSend(
            //     channel: '#deployments',
            //     color: 'good',
            //     message: "✅ Deployment successful! Image: ${GCR_IMAGE}"
            // )
        }
        
        // Jika gagal
        failure {
            echo '❌ Pipeline failed!'
            
            // Bisa tambahkan notifikasi di sini
            // slackSend(
            //     channel: '#deployments',
            //     color: 'danger',
            //     message: "❌ Deployment failed! Check Jenkins for details."
            // )
        }
        
        // Jika tidak stabil (ada warning)
        unstable {
            echo '⚠️ Pipeline completed with warnings!'
        }
    }
}


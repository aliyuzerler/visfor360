import { Badge, Card, CardBody, CardHeader, CardTitle } from '@/components/ui/indicators';
import { EmptyState } from '@/components/ui/page';
import { AppShell } from '@/components/app/app-shell';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

/**
 * Genel Bakış (Faz 1, Prompt 10'da gerçek veriye bağlanır).
 * Şu anki hali wireframe'dir: her kart, hangi gerçek verinin geleceğini açıklar;
 * sahte KPI gösterilmez. Veri yoksa empty state görüntülenir.
 */
export default function Dashboard() {
    return (
        <>
            <Head title="Genel Bakış" />
            <AppShell title="Genel Bakış">
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    <h2 className="text-lg font-semibold text-foreground">Genel Bakış</h2>
                    <Badge variant="warning">Wireframe — Faz 1 sonunda gerçek veri</Badge>
                </div>
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Bekleyen onaylar</CardTitle>
                        </CardHeader>
                        <CardBody>
                            <EmptyState
                                title="Onay bekleyen iş yok"
                                description="Fırsat onayları, hakediş onayları ve aşama geçişleri burada listelenir (Faz 1/2 modülleriyle aktifleşir)."
                            />
                        </CardBody>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Geciken işler</CardTitle>
                        </CardHeader>
                        <CardBody>
                            <EmptyState
                                title="Geciken iş yok"
                                description="Son tarihi geçmiş görev ve proje aşamaları burada öncelik sırasıyla listelenir."
                            />
                        </CardBody>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Evrak uyarıları</CardTitle>
                        </CardHeader>
                        <CardBody>
                            <EmptyState
                                title="Evrak uyarısı yok"
                                description="Eksik zorunlu evraklar ve süresi yaklaşan/geçen belgeler müşteri kartlarında buraya düşer (Evrak modülü Faz 1)."
                            />
                        </CardBody>
                    </Card>
                    <Card className="xl:col-span-3">
                        <CardHeader>
                            <CardTitle>Nakit özeti</CardTitle>
                        </CardHeader>
                        <CardBody>
                            <EmptyState
                                title="Finans verisi yok"
                                description="Gelir-gider, tahsilat/ödeme ve runway görünümü Finans modülü (Faz 1, Prompt 09) tamamlandığında gerçek kayıtlardan hesaplanır. Onaylı müşteri formülü gelene kadar tahmin gösterilmez."
                            />
                        </CardBody>
                    </Card>
                </div>
            </AppShell>
        </>
    );
}

<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;
use League\Csv\Statement;
use Carbon\Carbon;

class ImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvPath;
    protected $companyId;

    public function __construct($csvPath, $companyId)
    {
        $this->csvPath = $csvPath;
        $this->companyId = $companyId;
    }

    public function handle()
    {
        try {
            $csvPath = storage_path('app/' . $this->csvPath);
            if (!file_exists($csvPath)) {
                \Log::error('Arquivo não encontrado: ' . $csvPath);
            }

            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setHeaderOffset(0); // skip first line

            $stmt = (new Statement());
            $records = $stmt->process($csv);

            foreach ($records as $record) {
                // create User
                $user = User::create([
                    'name' => $record['nome'],
                    'email' => $record['email'],
                    'password' => Hash::make('Trocar@me'), // default password
                    'company_id' => $this->companyId
                ]);

                // format admissão
                $admissionDate = Carbon::createFromFormat('d/m/Y', $record['data de admissão'])->format('Y-m-d');

                // format telefone
                $phone = preg_replace('/\D/', '', $record['telefone']); // Remove caracteres não numéricos

                // create Employee 
                Employee::create([
                    'responsibility' => $record['cargo'],
                    'admission_at' => $admissionDate,
                    'phone' => $phone,
                    'user_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to import employees: ' . $e->getMessage());
            // Opcional: rethrow ou manipular conforme sua lógica (por exemplo, falhar a importação ou voltar)
        }
    }
}
